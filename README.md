# Queue

[![Master branch build status][ico-build]][travis]
[![Published version][ico-package]][package]
[![PHP ~5.4][ico-engine]][lang]
[![MIT Licensed][ico-license]][license]

This library provides a flexible abstraction layer for working with queues. It
can be installed in whichever way you prefer, but we recommend
[Composer][package].
```json
{
    "require": {
        "graze/queue": "*"
    }
}
```

## Documentation
Queue operations center around lists of Message objects. Whether you're sending
one or multiple Messages, it's always an array. Workers work only on one Message
object at a time, whether a list of one or multiple is received from the queue.
```php
<?php
use Graze\Queue\Client;
use Graze\Queue\Adapter\ArrayAdapter;
use Graze\Queue\Message\MessageInterface;

// Create client
$client = new Client(new ArrayAdapter());

// Send message(s)
$client->send([
    $client->create('123abc'),
    $client->create('456def')
]);

// Receive
$client->receive(function (MessageInterface $message) {
    // Do some work
});
```

### Adapters
The Adapter object is used to fulfil the low level requests to the queue
provider. Currently supported queue providers are:
 - [Array](src/Adapter/ArrayAdapter.php)
 - [AWS SQS](src/Adapter/SqsAdapter.php) (with the [AWS SDK](http://aws.amazon.com/sdk-for-php/))

### Handlers
The Handler object is used to execute worker callables with a list of received
messages and handle Acknowledgement. The current handlers are:
 - [Batch Acknowledgement](src/Handler/BatchAcknowledgementHandler.php) to acknowledge batches
 - [Eager Acknowledgement](src/Handler/EagerAcknowledgementHandler.php) to acknowledge immediately after work
 - [Null Acknowledgement](src/Handler/NullAcknowledgementHandler.php) for development

```php
<?php
use Graze\Queue\Client;
use Graze\Queue\Adapter\ArrayAdapter;
use Graze\Queue\Handler\BatchAcknowledgementHandler;
use Graze\Queue\Message\MessageInterface;

// Create client with the Batch Acknowledgement Handler
$client = new Client(new ArrayAdapter(), [
    'handler' => new BatchAcknowledgeHandler()
]);

// Receive a maximum of 10 messages
$client->receive(function (MessageInterface $message) {
    // Do some work
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
<?php
use Graze\Queue\Client;
use Graze\Queue\Adapter\ArrayAdapter;
use Graze\Queue\Handler\BatchAcknowledgementHandler;
use Graze\Queue\Message\MessageInterface;

// Create client with the Batch Acknowledgement Handler
$client = new Client(new ArrayAdapter(), [
    'handler' => new BatchAcknowledgeHandler(100) // Acknowledge after 100 messages
]);

// Poll until `$done()` is called
$client->receive(function (MessageInterface $message, Closure $done) {
    // Do some work

    // You should always define a break condition (i.e. timeout, expired session, etc)
    if ($breakCondition) $done();
}, null);
```

## Contributing
We accept contributions to the source via Pull Request, but passing unit tests
must be included before it will be considered for merge.
```bash
$ curl -O https://raw.githubusercontent.com/adlawson/vagrantfiles/master/php/Vagrantfile
$ vagrant up
$ vagrant ssh
$ cd /srv

$ composer install
$ vendor/bin/phpunit
```

### License
The content of this library is released under the **MIT License** by
**Nature Delivered Ltd**.<br/> You can find a copy of this license at
http://www.opensource.org/licenses/mit or in [`LICENSE`][license]

<!-- Links -->
[travis]: https://travis-ci.org/graze/queue
[lang]: http://php.net
[package]: https://packagist.org/packages/graze/queue
[ico-license]: http://img.shields.io/packagist/l/graze/queue.svg?style=flat
[ico-package]: http://img.shields.io/packagist/v/graze/queue.svg?style=flat
[ico-build]: http://img.shields.io/travis/graze/queue/master.svg?style=flat
[ico-engine]: http://img.shields.io/badge/php-~5.4-8892BF.svg?style=flat
[license]: LICENSE
