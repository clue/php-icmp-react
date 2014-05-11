<?php

use Clue\React\Icmp\Factory;
use React\EventLoop\StreamSelectLoop;

class FactoryTest extends TestCase
{
    public function testConstructor()
    {
        $factory = new Factory();

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $factory->getLoop());

        return $factory;
    }

    /**
     *
     * @param Factory $factory
     * @depends testConstructor
     */
    public function testIcmp(Factory $factory)
    {
        try {
            $icmp = $factory->createIcmp4();
        }
        catch (Exception $e) {
            if ($e->getCode() === SOCKET_EPERM) {
                // skip if not root
                return $this->markTestSkipped('No access to create socket (only root can do so)');
            }
            throw $e;
        }
        $this->assertInstanceOf('Clue\React\Icmp\Icmp', $icmp);
    }

    public function testLoopArgument()
    {
        $loop = new StreamSelectLoop();

        $factory = new Factory($loop);

        $this->assertSame($loop, $factory->getLoop());
    }
}
