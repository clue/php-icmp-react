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
use Socket\React\Datagram\Factory;
use \Exception;

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

    public function __construct(LoopInterface $loop)
    {
        $factory = new Factory($loop);
        $this->socket = $factory->createIcmp4();
        $this->socket->on('message', array($this, 'handleMessage'));

        $this->messageFactory = new MessageFactory();
    }

    /**
     * send ICMP echo request and wait for ICMP echo response
     *
     * @param string $remote remote host or IP address to ping
     * @return React\Promise\PromiseInterface
     */
    public function ping($remote)
    {
        $that = $this;
        $messageFactory = $this->messageFactory;

        return $this->resolve($remote)->then(function ($remote) use ($that, $messageFactory) {
            $ping = $messageFactory->createMessagePing();

            $that->sendMessage($ping, $remote);

            return $ping->promisePong($that);
        });
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
