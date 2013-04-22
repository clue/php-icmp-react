<?php

namespace Icmp;

use Icmp\Message;
use Iodophor\Io\StringReader;
use Iodophor\Io\StringWriter;
use \InvalidArgumentException;
use \Exception;

class MessageFactory
{
    public function createFromString($message)
    {
        $io = new StringReader($message);
        $type     = $io->readUInt8();
        $code     = $io->readUInt8();
        $checksum = $io->readUInt16BE();

//         if ($data['type'] === self::TYPE_ECHO_REQUEST || $data['type'] === self::TYPE_ECHO_RESPONSE) {
//             $data['id'] = $io->readUInt16BE();
//             $data['sequence'] = $io->readUInt16BE();
//         }

        $headerData = $io->readUInt32BE();

        $payload = (string)substr($message, $io->getOffset());

        return new Message($type, $code, $checksum, $headerData, $payload);
    }

//     public function createMessage($type, $code, $headerData, $payloadData = '')
//     {
//         return new Message($type & 0xFFFF, $code & 0xFFFF, null, $headerData & 0xFFFFFFFF, $payloadData);
//     }

    public function createMessagePing()
    {
        $headerData = (($this->getPingId() & 0xFFFF) << 16) + ($this->getPingSequence() & 0xFFFF);

        return new Message(Message::TYPE_ECHO_REQUEST, 0, null, $headerData, $this->getPingData());
    }

    public function createMessagePong(Message $ping)
    {
        if ($ping->getType() !== Message::TYPE_ECHO_REQUEST) {
            throw new InvalidArgumentException();
        }

        return new Message(Message::TYPE_ECHO_RESPONSE, 0, null, $ping->getHeader(), $ping->getPayload());
    }

    protected function getPingId()
    {
        return mt_rand(0, 65535);
    }

    protected function getPingSequence()
    {
        return mt_rand(0, 65535);
    }

    protected function getPingData()
    {
        return 'ping' . mt_rand(0,9);
    }
}
