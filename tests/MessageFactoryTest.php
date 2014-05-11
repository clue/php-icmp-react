<?php

use Clue\React\Icmp\MessageFactory;
use Clue\React\Icmp\Message;

class MessageFactoryTest extends TestCase
{

    /**
     *
     * @var MessageFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new MessageFactory();
    }

    public function testCreateFromString()
    {
        $string = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
        //           |    |   \  /    \  /    \  / \  ____/
        //          type  |    \/      \/      \/   \/
        //               code  |       id      |    payload+
        //                    checksum        sequence

        $message = $this->factory->createFromString($string);
        $this->assertInstanceOf('Clue\React\Icmp\Message', $message);

        $this->assertEquals(Message::TYPE_ECHO_REQUEST, $message->getType());
        $this->assertEquals(0, $message->getCode());
        $this->assertTrue($message->isChecksumValid());

        $this->assertEquals(0, $message->getHeader());
        $this->assertEquals(0, $message->getPingId());
        $this->assertEquals(0, $message->getPingSequence());

        $this->assertEquals('PingHost', $message->getPayload());

        $this->assertEquals($string, $message->getMessagePacket());
    }

//     public function testCreateFromStringFailsForInvalidMessage()
//     {
//         $string = "?";

//         $factory = new MessageFactory();

//         $message = $factory->createFromString($string);
//     }

    public function testCreateMessagePing()
    {
        $message = $this->factory->createMessagePing();

        $this->assertInstanceOf('Clue\React\Icmp\Message', $message);

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
        $message = $this->factory->createMessagePong($ping);

        $this->assertInstanceOf('Clue\React\Icmp\Message', $message);

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

        $this->factory->createMessagePong($pingInvalid);
    }
}