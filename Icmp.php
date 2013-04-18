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

    public function createMessage($type, $code, $header, $payload = '')
    {
        if (strlen($header) !== 4) {
            throw new Exception();
        }
        $io = new StringWriter();
        $io->writeInt8($type);
        $io->writeInt8($code);
        $io->writeInt16BE(0);

        $message = $io->toString() . $header . $payload;

        $checksum = $this->getChecksum($message);

        $io->setOffset(2);
        $io->writeInt16BE($checksum);

        $message = $io->toString() . $header . $payload;

        return $message;
    }

    /**
     * compute internet checksum
     *
     * @param string $data
     * @return int 16bit checksum integer
     * @link http://tools.ietf.org/html/rfc1071#section-4.1
     * @todo check result for odd number of bytes?
     */
    private function getChecksum($data)
    {
        $bit = unpack('n*', $data);

        // ignore any checksum already set in the message
        $bit[2] = 0;

        $sum = array_sum($bit);

        if (strlen($data) % 2) {
            $temp = unpack('C*', $data[strlen($data) - 1]);
            $sum += $temp[1];
        }

        while ($sum >> 16) {
            $sum = ($sum & 0xffff) + ($sum >> 16);
        }

        return (~$sum & 0xffff);
    }
}
