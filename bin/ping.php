#!/usr/bin/env php
<?php

(@include_once __DIR__.'/../vendor/autoload.php') OR (print('ERROR: Installation incomplete, please see README' . PHP_EOL) AND exit(1));

$remote = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : null;

if ($remote === null) {
    echo 'No remote target given' . PHP_EOL;
    exit(1);
}

$loop = React\EventLoop\Factory::create();

$icmp = new Icmp\Icmp($loop);

echo 'Pinging "' . $remote . '"...' . PHP_EOL;
$icmp->ping($remote)->then(function ($time) use ($icmp) {
    echo 'Success after ' . round($time, 3) . 's' . PHP_EOL;
    $icmp->pause();
}, function (Exception $error) {
    echo 'Error: ' . $error->getMessage() . PHP_EOL;
    exit(1);
});

$loop->run();
