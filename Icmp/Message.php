<?php

namespace Icmp;

use Icmp\Icmp;
use React\Promise\Deferred;
use Iodophor\Io\StringWriter;
use \Exception;

class Message
{
    const TYPE_ECHO_REQUEST = 128;
    const TYPE_ECHO_REPLY = 129;

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

    public function getHeaderPointer()
    {
        return (($this->header >> 24) & 0xFF);
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getLength()
    {
        return 8 + strlen($this->payload);
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
     */
    public function getChecksumCalculated()
    {
        $data  = $this->getMessagePacket();

        // odd length => append null byte
        if (strlen($data) % 2) {
            $data .= "\x00";
        }

        $bit = unpack('n*', $data);

        // ignore any checksum already set in the message
        $bit[2] = 0;

        $sum = array_sum($bit);

        while ($sum >> 16) {
            $sum = ($sum & 0xffff) + ($sum >> 16);
        }

        return (~$sum & 0xffff);
    }
}
