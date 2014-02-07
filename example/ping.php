<?php

include_once __DIR__.'/../vendor/autoload.php';

$remote = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'github.com';

$timeout = 3.0;

$loop = React\EventLoop\Factory::create();

$icmp = new Icmp\Icmp($loop);

echo 'Pinging "' . $remote . '"...' . PHP_EOL;
$icmp->ping($remote, $timeout)->then(function ($time) use ($icmp) {
    echo 'Success after ' . round($time, 3) . 's' . PHP_EOL;
    $icmp->pause();
}, function (Exception $error) {
    echo 'Error: ' . $error->getMessage() . PHP_EOL;
    exit(1);
});

$loop->run();
