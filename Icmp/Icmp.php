<?php

namespace Icmp;

use Icmp\MessageFactory;
use Icmp\Message;
use Iodophor\Io\StringWriter;
use Iodophor\Io\StringReader;
use React\Promise\Deferred;
use React\Promise\When;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use Socket\React\Datagram\Factory as SocketFactory;
use \Exception;
use Clue\Promise\React\Timeout;
use React\EventLoop\Timer\Timer;
use Socket\React\Datagram\Socket as Socket;
use Clue\Hexdump\Hexdump;

/**
 * ICMP (Internet Control Message Protocol) bindings for reactphp
 *
 * @author clue
 * @link https://github.com/clue/icmp-react
 * @link http://tools.ietf.org/html/rfc792
 */
class Icmp extends EventEmitter
{
    /** @var Socket\React\Datagram\Datagram */
    private $socket;

    private $messageFactory;

    private $loop;

    public function __construct(LoopInterface $loop, Socket $socket = null)
    {
        $this->loop = $loop;

        if ($socket === null) {
            $factory = new SocketFactory($loop);
            $socket = $factory->createIcmp4();
        }

        $this->socket = $socket;
        $this->socket->on('message', array($this, 'handleMessage'));

        $this->messageFactory = new MessageFactory();
    }

    /**
     * send ICMP echo request and wait for ICMP echo response
     *
     * @param string $remote  remote host or IP address to ping
     * @param float  $timeout maximum time in seconds to wait to receive pong
     * @return React\Promise\PromiseInterface resolves with ping round trip time (RTT) in seconds or rejects with Exception
     */
    public function ping($remote, $timeout = 5.0)
    {
        $that           = $this;
        $messageFactory = $this->messageFactory;
        $listener       = null;
        $start          = microtime(true);

        $result = new Deferred();

        $timer = $this->loop->addTimer($timeout, function(Timer $timer) use ($that, $result, &$listener) {
            if ($listener) {
                $that->removeListener(Message::TYPE_ECHO_REPLY, $listener);
            }

            $result->reject(new Exception('Timed out after ' . $timer->getInterval() . ' seconds'));
        });

        $promise = $this->resolve($remote)->then(function ($remote) use (
            $that,
            $messageFactory,
            $start,
            $timer,
            $result,
            &$listener
        ) {
            if (!$timer->isActive()) {
                // timeout occured while resolving hostname => already canceled, so don't even send a message
                return;
            }

            $ping = $messageFactory->createMessagePing();

            $that->sendMessage($ping, $remote);

            $id       = $ping->getPingId();
            $sequence = $ping->getPingSequence();
            // TODO: check payload

            $listener = function (Message $pong) use (
                $id,
                $sequence,
                &$listener,
                $that,
                $result,
                $timer,
                $start
            ) {
                if ($pong->getPingId() === $id && $pong->getPingSequence() === $sequence) {
                    $that->removeListener(Message::TYPE_ECHO_REPLY, $listener);
                    $timer->cancel();

                    $time = microtime(true) - $start;
                    if ($time < 0) {
                        $time = 0;
                    } elseif ($time > $timer->getInterval()) {
                        $time = $timeout;
                    }

                    $result->resolve($time);
                }
            };
            $that->on(Message::TYPE_ECHO_REPLY, $listener);
        });

        return $result->promise();
    }

    public function pause()
    {
        $this->socket->pause();
    }

    public function resume()
    {
        $this->socket->resume();
    }

    public function handleMessage($message, $peer)
    {
        $ip = substr($message, 0, 20);
        $icmp = substr($message, 20);

//         echo 'received from ' . $peer . PHP_EOL;
//         $hex = new Hexdump();
//         echo $hex->dump($icmp);

        try {
            $message = $this->messageFactory->createFromString($icmp);
        }
        catch (Exception $ignore) {
            return;
        }

        $checksum = $message->getChecksumCalculated();
        if ($checksum !== $message->getChecksum()) {
            //             var_dump('DROP! Checksum invalid! received', $data['checksum'], 'calculated', $checksum);
        }

        $this->emit($message->getType(), array($message, $peer));
        $this->emit('message', array($message, $peer));
    }

    public function sendMessage(Message $message, $remoteAddress)
    {
        //         echo 'send to ' . $remoteAddress . PHP_EOL;
        //         $hex = new Hexdump();
        //         $hex->dump($message);

        $this->socket->send($message->getMessagePacket(), $remoteAddress);
    }

    private function resolve($host)
    {
        return When::resolve($host);
    }
}
