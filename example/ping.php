<?php

include_once __DIR__.'/../vendor/autoload.php';

$remote = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'github.com';

$timeout = 3.0;

$factory = new Icmp\Factory();
$icmp = $factory->createIcmp4();

echo 'Pinging "' . $remote . '"...' . PHP_EOL;
$icmp->ping($remote, $timeout)->then(function ($time) use ($icmp) {
    echo 'Success after ' . round($time, 3) . 's' . PHP_EOL;
}, function (Exception $error) {
    echo 'Error: ' . $error->getMessage() . PHP_EOL;
})->then(array($icmp, 'pause'));

$factory->getLoop()->run();
