<?php

namespace Icmp;

use React\EventLoop\LoopInterface;
use Socket\React\EventLoop\SocketSelectLoop;
use Socket\React\Datagram\Factory as SocketFactory;

class Factory
{
    private $loop;
    private $socketFactory;

    public function __construct(LoopInterface $loop = null, SocketFactory $socketFactory = null)
    {
        if ($loop === null) {
            $loop = new SocketSelectLoop();
        }

        if ($socketFactory === null) {
            $socketFactory = new SocketFactory($loop);
        }

        $this->loop = $loop;
        $this->socketFactory = $socketFactory;
    }

    public function createIcmp4()
    {
        return new Icmp($this->loop, $this->socketFactory->createIcmp4());
    }

    public function getLoop()
    {
        return $this->loop;
    }
}
