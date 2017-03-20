# Queue

<img align="right" src="http://i.giphy.com/100mhETqKYJNf2.gif" width="260 "/>

[![PHP ~5.5](https://img.shields.io/badge/php-%3E%3D5.5-8892BF.svg)](https://secure.php.net)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/graze/dog-statsd.svg?style=flat-square)](https://packagist.org/packages/graze/dog-statsd)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/graze/dog-statsd/master.svg?style=flat-square)](https://travis-ci.org/graze/dog-statsd)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/graze/dog-statsd.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/dog-statsd/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/graze/dog-statsd.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/dog-statsd)
[![Total Downloads](https://img.shields.io/packagist/dt/graze/dog-statsd.svg?style=flat-square)](https://packagist.org/packages/graze/dog-statsd)

This library provides a flexible abstraction layer for working with queues.

It can be installed in whichever way you prefer, but we recommend [Composer][package].

`~$ composer require graze/queue`

## Documentation

Queue operations center around lists of Message objects. Whether you're sending
one or multiple Messages, it's always an array. Workers work only on one Message
object at a time, whether a list of one or multiple is received from the queue.

```php
use Aws\Sqs\SqsClient;
use Graze\Queue\Adapter\SqsAdapter;
use Graze\Queue\Client;
use Graze\Queue\Message\MessageInterface;

$client = new Client(new SqsAdapter(new SqsClient([
    'region'  => 'us-east-1',
    'version' => '2012-11-05',
    'credentials' => [
        'key'    => 'ive_got_the_key',
        'secret' => 'ive_got_the_secret'
    ],
]), 'queue_name'));

// Producer
$client->send([
    $client->create('foo'),
]);

// Consumer
$client->receive(function (MessageInterface $msg) {
    var_dump($msg->getBody());
    var_dump($msg->getMetadata()->getAll());
});
```

### Adapters

The Adapter object is used to fulfill the low level requests to the queue provider.

Currently supported queue providers are:

 - [Array](src/Adapter/ArrayAdapter.php)
 - [AWS SQS](src/Adapter/SqsAdapter.php) (with the [AWS SDK](http://aws.amazon.com/sdk-for-php/))

### Handlers

The Handler object is used to execute worker callables with a list of received messages and handle Acknowledgement.

The current handlers are:

 - [Batch Acknowledgement](src/Handler/BatchAcknowledgementHandler.php) to acknowledge batches
 - [Eager Acknowledgement](src/Handler/EagerAcknowledgementHandler.php) to acknowledge immediately after work
 - [Null Acknowledgement](src/Handler/NullAcknowledgementHandler.php) for development

```php
use Graze\Queue\Client;
use Graze\Queue\Adapter\ArrayAdapter;
use Graze\Queue\Handler\BatchAcknowledgementHandler;
use Graze\Queue\Message\MessageInterface;

// Create client with the Batch Acknowledgement Handler.
$client = new Client(new ArrayAdapter(), [
    'handler' => new BatchAcknowledgementHandler(),
]);

// Receive a maximum of 10 messages.
$client->receive(function (MessageInterface $message) {
    // Do some work.
}, 10);
```

### Polling

Polling a queue is supported by passing `null` as the limit argument to the
`receive` method. The second argument given to your worker is a `Closure` you
should use to stop polling when you're finished working. Make sure you use a
handler that acknowledges work effectively too!

Note that the individual Adapter objects may decide to stop polling at any time.
A likely scenario where it may stop would be if the queue is of finite length
and all possible messages have been received.

```php
use Graze\Queue\Client;
use Graze\Queue\Adapter\ArrayAdapter;
use Graze\Queue\Handler\BatchAcknowledgementHandler;
use Graze\Queue\Message\MessageInterface;

// Create client with the Batch Acknowledgement Handler.
$client = new Client(new ArrayAdapter(), [
    'handler' => new BatchAcknowledgeHandler(100), // Acknowledge after 100 messages.
]);

// Poll until `$done()` is called.
$client->receive(function (MessageInterface $message, Closure $done) {
    // Do some work.

    // You should always define a break condition (i.e. timeout, expired session, etc).
    if ($breakCondition) $done();
}, null);
```

## License

The content of this library is released under the **MIT License** by **Nature Delivered Ltd.**

You can find a copy of this license in [`LICENSE`][license] or at http://opensource.org/licenses/mit.
