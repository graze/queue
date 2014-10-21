# Queue

This library provides a flexible abstraction layer for working with queues.

It can be installed in whichever way you prefer, but we recommend [Composer][package].
```json
{
    "require": {
        "graze/queue": "*"
    }
}
```

## Documentation
Queue operations center around lists of Message objects. Whether you're sending
one or muliple Messages, it's always an array. Workers work only on one Message
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

### Acknowledgement
Acknowledgement of completed work is carried out by an Acknowledge Policy. The
Policy object is notified of the Message *after* the worker successfully
completes and again when the whole list of Messages have been worked on. It's up
to the Acknowledge Policy to decide exactly when to send the acknowledgement to
the queue.
```php
<?php
use Graze\Queue\Client;
use Graze\Queue\AcknowledgePolicy\BatchAcknowledgePolicy;
use Graze\Queue\Adapter\ArrayAdapter;
use Graze\Queue\Message\MessageInterface;

// Create client with the Batch Acknowledge Policy
// This policy acknowledges with the queue once the batch has completed
$client = new Client(new ArrayAdapter(), new BatchAcknowledgePolicy());

// Receive
$limit = 10;
$client->receive(function (MessageInterface $message) {
    // Do some work
}, $limit);
```
