# clue/icmp-react [![Build Status](https://travis-ci.org/clue/php-icmp-react.svg?branch=master)](https://travis-ci.org/clue/php-icmp-react)

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
one).

### Factory

The `Clue\React\Icmp\Factory` is a convenient wrapper that helps you initialize the correct
loop and an ICMP handler.

#### Simple factory

Passing an existing loop instance to the factory is optional. If you're new to
the concept of an event loop and/or only care about handling ICMP messages, then
you're recommend to let the factory do the work for you and let it create the
recommended loop implementation:

```php
$factory = new Clue\React\Icmp\Factory();
$icmp = $factory->createIcmp4();
```

You will have to access and later run its loop like this:

```php
$loop = $factory->getLoop();

// place your ICMP calls here, see below...

// important: run the loop in order to actually do anything
$loop->run();
```

If you do not pass an existing event loop (like above), one will be created for
you that is suitable (and reasonably fast) to handle an ICMP socket. However,
due to the nature of ICMP's low-level network access, this loop is not suitable
to add other stream resources to it. In particular, this means you should expect
it to reject just about any normal stream / connection usually found in reactphp.

#### Using an existing event loop

If you only want to handle ICMP sockets, the above limitations do not really
apply to you at all. If however you also want to use the same event loop for
other streams, you can also pass in an existing event loop instance to the
factory like this:

```php
$loop = React\EventLoop\Factory::create();
$factory = new Clue\React\Icmp\Factory($loop);
```

While this will allow you to attach any number of streams to the loop, this
approach usually does not natively support ICMP's low-level sockets. As such,
instead of actually setting up an event listener in the given loop internally,
it will have to set up a timer to periodically check the ICMP socket using an
hidden inner event loop. This is actually only a small implementation detail
you'll likely not have to worry about as this happens completely transparent.
However, while relying on a periodic timer (interval of 10ms, i.e. 100 checks
per second) will only have a small impact on CPU usage, this will have a
noticable effect on ICMP response times. So for example expect a `ping()` to
take an additional 10-20ms.

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
$icmp->on('message', function (Clue\React\Icmp\Message $message, $peerAddress) {
    echo 'Message type ' . $message->getType() . ' from ' . $peerAddress . PHP_EOL;
});
```

### Send messages

If you want to send arbitrary ICMP messages, you can either construct a
`Message` object yourself like this

```php
$message = new Clue\React\Icmp\Message($type, $code, $checksum, $header, $payload)
```

or you can use the `MessageFactory` to create common types of messages like this

```php
$factory = new Clue\React\Icmp\MessageFactory();
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
        "clue/icmp-react": "0.1.*"
    }
}
```

## Tests

To run the test suite, you need PHPUnit. Go to the project root and run:

```bash
$ phpunit tests
```

> Note: The test suite contains tests for ICMP sockets which require root access on unix/linux systems. Therefor some tests will be skipped unless you run `sudo phpunit tests` to execute the full test suite.


## License

MIT
