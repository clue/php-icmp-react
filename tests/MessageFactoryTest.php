<?php

use Icmp\MessageFactory;
use Icmp\Message;

class MessageFactoryTest extends TestCase
{
    public function testCreateFromString()
    {
        $string = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
        //           |    |   \  /    \  /    \  / \  ____/
        //          type  |    \/      \/      \/   \/
        //               code  |       id      |    payload+
        //                    checksum        sequence

        $factory = new MessageFactory();

        $message = $factory->createFromString($string);
        $this->assertInstanceOf('Icmp\Message', $message);

        $this->assertEquals(Message::TYPE_ECHO_REQUEST, $message->getType());
        $this->assertEquals(0, $message->getCode());
        $this->assertTrue($message->isChecksumValid());

        $this->assertEquals(0, $message->getHeader());
        $this->assertEquals(0, $message->getPingId());
        $this->assertEquals(0, $message->getPingSequence());

        $this->assertEquals('PingHost', $message->getPayload());

        $this->assertEquals($string, $message->getMessagePacket());
    }

    public function testCreateMessagePing()
    {
        $factory = new MessageFactory();

        $message = $factory->createMessagePing();

        $this->assertInstanceOf('Icmp\Message', $message);

        $this->assertEquals(Message::TYPE_ECHO_REQUEST, $message->getType());
        $this->assertEquals(0, $message->getCode());
        $this->assertTrue($message->isChecksumValid());

        return $message;
    }

    /**
     *
     * @param Message $ping
     * @depends testCreateMessagePing
     */
    public function testCreateMessagePong(Message $ping)
    {
        $factory = new MessageFactory();

        $message = $factory->createMessagePong($ping);

        $this->assertInstanceOf('Icmp\Message', $message);

        $this->assertEquals(Message::TYPE_ECHO_REPLY, $message->getType());
        $this->assertEquals(0, $message->getCode());
        $this->assertEquals($ping->getPingId(), $message->getPingId());
        $this->assertEquals($ping->getPingSequence(), $message->getPingSequence());
        $this->assertTrue($message->isChecksumValid());
        $this->assertEquals($ping->getPayload(), $message->getPayload());
    }

    /**
     * assert that trying to create a pong message fails if the given message is not a ping message
     *
     * @expectedException InvalidArgumentException
     */
    public function testCreateMessagePongFailsForNonPingMessage()
    {
        $pingInvalid = new Message(1, 2, 3, 4);

        $factory = new MessageFactory();

        $factory->createMessagePong($pingInvalid);
    }
}