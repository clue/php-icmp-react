# clue/icmp-react [![Build Status](https://travis-ci.org/clue/icmp-react.png?branch=master)](https://travis-ci.org/clue/icmp-react)

Simple async lowlevel ICMP (ping) messages for react

## Usage

Once clue/icmp-react is [installed](#install), you can use its `bin/ping.php` via command line like this:

```
$ sudo php bin/ping.php www.google.com
```

> Note: Please note the `sudo` there. Opening raw ICMP sockets requires root access!

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
