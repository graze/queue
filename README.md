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

### Acknowledgement
Acknowledgement of completed work is managed by a Handler. The Handler object
applies a given worker to a list of Messages and sends acknowledgement via the
Adapter. It's up to the Handler to determine exactly when to send the
acknowledgement to the queue (i.e. after each message, after the whole batch,
etc).
```php
<?php
use Graze\Queue\Client;
use Graze\Queue\Adapter\ArrayAdapter;
use Graze\Queue\Handler\BatchAcknowledgementHandler;
use Graze\Queue\Message\MessageInterface;

// Create client with the Batch Acknowledge Handler
// This policy acknowledges with the queue once the batch has completed
$client = new Client(new ArrayAdapter(), [
    'handler' => new BatchAcknowledgeHandler()
]);

// Receive
$limit = 10;
$client->receive(function (MessageInterface $message) {
    // Do some work
}, $limit);
```
