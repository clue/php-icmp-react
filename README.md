# clue/icmp-react [![Build Status](https://travis-ci.org/clue/icmp-react.png?branch=master)](https://travis-ci.org/clue/icmp-react)

Simple async lowlevel ICMP (ping) messaging library built on top of react

## Usage

Once clue/icmp-react is [installed](#install), you can run any of its examples
via command line like this:

```
$ sudo php example/ping.php www.google.com
```

> Note: Please note the `sudo` there. Opening raw ICMP sockets requires root access!

The provided examples should give you a good overview of how this library works.
As such, the following guide assumes some familiarity with the examples and
explains them a bit more in detail.

This library is built on top of reactphp in order to follow an async (non-blocking)
paradigm. This is done so that you can send and receive multiple messages in
parallel without blocking each other.

For this to work, you will need to pass it an event loop (or create a new
one as in the following example):

```php
// initialize a loop and our ICMP library
$loop = React\EventLoop\Factory::create();
$icmp = new Icmp\Icmp($loop);

// place your ICMP calls here, see below...

// important: run the loop in order to actually do anything
$loop->run();
```

### Ping

Probably the most common use of this library is to send *ICMP echo requests*
(i.e. *ping messages*), so it provides a convenient promise-based API:

```php
$icmp->ping($remote, $timeout);
```

Keep in mind that this is an async method, i.e. the ping message will only be
queued to be sent. This is done so that you can send multiple ping messages in
parallel without blocking each other and also matching the incoming ping replies
to their corresponding ping requests.

You will probably use this code somewhat like this:

```php
$icmp->ping('github.com', 3.0)->then(function ($time) {
    echo 'Success after ' . $time . ' seconds';
}, function(Exception $error) {
    echo 'Nope, there was an error';
});
```

### Receive messages

The above code automatically sets up a temporary listener to check each incoming
message if it matches the expected ping result. You can also listen to any
incoming messages yourself like this:

```php
$icmp->on('message', function (Icmp\Message $message, $peerAddress) {
    echo 'Message type ' . $message->getType() . ' received from ' . $peerAddress . PHP_EOL;
});
```

### Send messages

If you want to send arbitrary ICMP messages, you can either construct a
`Message` object yourself like this

```php
$message = new Icmp\Message($type, $code, $checksum, $header, $payload)
```

or you can use the `MessageFactory` to create common types of messages like this

```php
$factory = new Icmp\MessageFactory();
$message = $factory->createMessagePing();
```

Next, you can send the message object like this:

```php
$icmp->sendMessage($message, '127.0.0.1');
```

This is an async operation, i.e. the message is only queued to be sent, so make
sure to actually start the loop (`$loop->run()`).
Also, you may likely (but not necessarily) want to wait for a response message,
so take a look at [receiving messages](#receive-messages).

## Install

The recommended way to install this library is [through composer](http://getcomposer.org). [New to composer?](http://getcomposer.org/doc/00-intro.md)

```JSON
{
    "require": {
        "clue/icmp-react": "dev-master"
    }
}
```

## License

MIT
