<?php

namespace Icmp;

use Icmp\Icmp;
use React\Promise\Deferred;
use Iodophor\Io\StringWriter;
use \Exception;

class Message
{
    const TYPE_ECHO_REQUEST = 8;
    const TYPE_ECHO_RESPONSE = 0;

    private $type;

    private $code;

    private $checksum;

    private $header;

    private $payload;

    public function __construct($type, $code, $checksum, $header, $payload = '')
    {
        $this->type     = $type;
        $this->code     = $code;
        $this->checksum = $checksum;
        $this->header   = $header;
        $this->payload  = $payload;

        if ($this->checksum === null) {
            $this->checksum = $this->getChecksumCalculated();
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getChecksum()
    {
        return $this->checksum;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getPingId()
    {
        return (($this->header >> 16) & 0xFFFF);
        // return _first_ 16bits of headerData
    }

    public function getPingSequence()
    {
        return ($this->header & 0xFFFF);
        // return _last_ 16bits of headerData
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getMessagePacket()
    {
        $io = new StringWriter();
        $io->writeInt8($this->type);
        $io->writeInt8($this->code);
        $io->writeInt16BE($this->checksum);
        $io->writeInt32BE($this->header);

        return $io->toString() . $this->payload;
    }

    public function isChecksumValid()
    {
        return ($this->checksum === $this->getChecksumCalculated());
    }

    /**
     * compute internet checksum for this message
     *
     * @return int 16bit checksum integer
     * @link http://tools.ietf.org/html/rfc1071#section-4.1
     * @todo check result for odd number of bytes?
     */
    public function getChecksumCalculated()
    {
        $data  = $this->getMessagePacket();

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

    public function promisePong(Icmp $icmp)
    {
        if ($this->type !== self::TYPE_ECHO_REQUEST) {
            throw new Exception('This message has to be of an ECHO_REQUEST (ping) in order to be able to wait for an ECHO_RESPONSE (pong)');
        }

        $deferred = new Deferred();

        $id       = $this->getPingId();
        $sequence = $this->getPingSequence();
        // TODO: check payload

        $listener = function (Message $pong) use ($deferred, $id, $sequence, &$listener, $icmp) {
            if ($pong->getPingId() === $id && $pong->getPingSequence() === $sequence) {
                $icmp->removeListener(Message::TYPE_ECHO_RESPONSE, $listener);
                $deferred->resolve();
            }
        };
        $icmp->on(Message::TYPE_ECHO_RESPONSE, $listener);

        return $deferred->promise();
    }
}
