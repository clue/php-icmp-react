<?php

include_once __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$icmp = new Icmp\Icmp($loop);

$n = 0;

$icmp->on('message', function ($data, $peerAddress) use (&$n) {
    echo 'Message #' . ++$n . ' received from ' . $peerAddress . ':' . PHP_EOL;
    var_dump($data);
    echo PHP_EOL;
});

// send two ICMP echo request messages in order to show some output
$icmp->ping('google.com');
$icmp->ping('yahoo.com');

$loop->run();
