<?php

class Icmp
{
    private $socket;
    
    private $sequence;
    
    const TYPE_ECHO_REQUEST = 8;
    const TYPE_ECHO_REPLAY = 0;
    
    public function __construct(LoopInterface $loop)
    {
        $factory = new Factory($loop);
        $this->socket = $factory->createIcmp4();
    }
    
    public function ping($remote)
    {
        
    }
    
    public function handleData($data)
    {
        
    }
    
    public function createMessage($type, $code, $header = null, $body = null)
    {
        
    }
    
    private function getChecksum($data)
    {
        $bit = unpack('n*', $data);
        $sum = array_sum($bit);
    
        if (strlen($data) % 2) {
            $temp = unpack('C*', $data[strlen($data) - 1]);
            $sum += $temp[1];
        }
    
        $sum = ($sum >> 16) + ($sum & 0xffff);
        $sum += ($sum >> 16);
    
        return pack('n*', ~$sum);
    }
    
}
