<?php

include_once __DIR__.'/../vendor/autoload.php';

function getTypeText($type)
{
    $types = array(
        3  => 'Destination Unreachable',
        11 => 'Time Exceeded',
        12 => 'Parameter Problem',
        4  => 'Source Quench',
        5  => 'Redirect',
        8  => 'Echo Request',
        0  => 'Echo Reply',
        13 => 'Timestamp Request',
        14 => 'Timestamp Reply',
        15 => 'Information Request',
        16 => 'Information Reply'
    );

    if (!isset($types[$type])) {
        return 'unknown type';
    }

    return $types[$type];
}

function getCodeText($type, $code)
{
    $messages = array(
        3 => array(
            0 => 'net unreachable',
            1 => 'host unreachable',
            2 => 'protocol unreachable',
            3 => 'port unreachable',
            4 => 'fragmentation needed and DF set',
            5 => 'source route failed'
        ),
        11 => array(
            0 => 'time to live exceeded in transit',
            1 => 'fragment reassembly time exceeded'
        ),
        13 => array(
            0 => 'pointer indicates the error'
        ),
        4 => 0,
        5 => array(
            0 => 'Redirect datagrams for the Network',
            1 => 'Redirect datagrams for the Host',
            2 => 'Redirect datagrams for the Type of Service and Network',
            3 => 'Redirect datagrams for the Type of Service and Host'
        ),
        8 => 0,
        0 => 0,
        13 => 0,
        14 => 0,
        15 => 0,
        16 => 0
    );
    if (isset($messages[$type]) && $messages[$type] === $code) {
        return '';
    }
    if (!isset($messages[$type][$code])) {
        return 'unknown code';
    }
    return $messages[$type][$code];
}

$factory = new Clue\React\Icmp\Factory();
$icmp = $factory->createIcmp4();

$n = 0;

$icmp->on('message', function (Clue\React\Icmp\Message $message, $peerAddress) use (&$n) {
    echo 'Message #' . ++$n . ' received from ' . $peerAddress . ':' . PHP_EOL;
    echo 'Type: ' . getTypeText($message->getType()) . ' (' . $message->getType() . ')' . PHP_EOL;
    echo 'Code: ' . getCodeText($message->getType(), $message->getCode()) . ' (' . $message->getCode() . ')' . PHP_EOL;
    echo 'Checksum: ' . $message->getChecksum() . ' (' . ($message->isChecksumValid() ? 'valid' : ('! MISMATCH ! Calculated ' . $message->getChecksumCalculated() .' !') ) . ')' . PHP_EOL;
    var_dump($message);
    echo PHP_EOL;
});

// send two ICMP echo request messages in order to show some output
$icmp->ping('google.com');
$icmp->ping('yahoo.com');

$factory->getLoop()->run();
