<?php

use Icmp\Message;
use Icmp\Icmp;
use React\EventLoop\Factory;

class MessageTest extends TestCase
{
    public function testGetters()
    {
        $message = new Message(1, 2, null, 3, 'payload');

        $this->assertEquals(1, $message->getType());
        $this->assertEquals(2, $message->getCode());

        $this->assertEquals(3, $message->getHeader());
        $this->assertEquals('payload', $message->getPayload());
    }


    /**
     * asdasd
     *
     * @expectedException Exception
     * @asd asd asd
     * @datus-coverage ok
     */
    public function testPongInvalid()
    {
        $message = new Message(1, 2, 3, 4);

        $loop = React\EventLoop\Factory::create();

        $icmp = new Icmp($loop);

        $message->promisePong($icmp);
    }
}
