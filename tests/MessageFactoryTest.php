<?php

use Icmp\MessageFactory;
use Icmp\Message;

class MessageFactoryTest extends TestCase
{
    public function testCreateFromString()
    {
        $string = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";

        $factory = new MessageFactory();

        $message = $factory->createFromString($string);

        $this->assertEquals(Message::TYPE_ECHO_REQUEST, $message->getType());
        $this->assertTrue($message->isChecksumValid());
        $this->assertEquals(0, $message->getPingSequence());
        $this->assertEquals('PingHost', $message->getPayload());
    }
}