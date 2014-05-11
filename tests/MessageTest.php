<?php

use Clue\React\Icmp\Message;
use Clue\React\Icmp\Icmp;
use React\EventLoop\Factory;

class MessageTest extends TestCase
{
    public function testGetters()
    {
        $message = new Message(1, 2, 3, 4, 'payload');

        $this->assertEquals(1, $message->getType());
        $this->assertEquals(2, $message->getCode());
        $this->assertEquals(3, $message->getChecksum());
        $this->assertEquals(4, $message->getHeader());
        $this->assertEquals('payload', $message->getPayload());


        $this->assertFalse($message->isChecksumValid());
    }

    /**
     * passing NULL as the checksum should automatically update the checksum to the valid, calculated value
     */
    public function testAutomaticMessageChecksum()
    {
        $message = new Message(1, 2, null, 3);

        $this->assertNotNull($message->getChecksum());
        $this->assertTrue($message->isChecksumValid());
    }
}
