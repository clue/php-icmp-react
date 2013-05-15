<?php

use Icmp\Icmp;

class IcmpTest extends TestCase
{
    public function testConstructor()
    {
        $loop = React\EventLoop\Factory::create();

        try {
            $icmp = new Icmp($loop);
        }
        catch (Exception $e) {
            if ($e->getCode() === SOCKET_EPERM) {
                // skip if not root
                return $this->markTestSkipped('No access to create socket (only root can do so)');
            }
            throw $e;
        }

        $this->assertInstanceOf('Icmp\Icmp', $icmp);

        return $icmp;
    }

    /**
     *
     * @depends testConstructor
     */
    public function testCanSend(Icmp $icmp)
    {
        $ret = $icmp->ping('www.google.com');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $ret);
    }
}
