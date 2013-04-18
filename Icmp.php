<?php

use Iodophor\Io\StringWriter;
use Iodophor\Io\StringReader;
use React\Promise\Deferred;
use React\Promise\When;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use Socket\React\Datagram\Factory;

class Icmp
{
    private $socketFactory;
    private $socket = null;

    const TYPE_ECHO_REQUEST = 8;
    const TYPE_ECHO_RESPONSE = 0;

    public function __construct(LoopInterface $loop)
    {
        $this->socketFactory = new Factory($loop);
    }

    public function ping($remote)
    {

    }

    public function getSocket()
    {
        if ($this->socket === null) {
            $this->socket = $this->socketFactory->createIcmp4();
            $this->socket->on('message', array($this, 'handleMessage'));
        }
        return $this->socket;
    }

    public function handleMessage($message, $peer)
    {

    }

    public function createMessage($type, $code, $header = null, $body = null)
    {

    }

    private function getChecksum($data)
    {
        $bit = unpack('n*', $data);
        $sum = array_sum($bit);

        if (strlen($data) % 2) {
            $temp = unpack('C*', $data[strlen($data) - 1]);
            $sum += $temp[1];
        }

        $sum = ($sum >> 16) + ($sum & 0xffff);
        $sum += ($sum >> 16);

        return pack('n*', ~$sum);
    }

}
