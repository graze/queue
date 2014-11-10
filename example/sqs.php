<?php
use Aws\Sqs\SqsClient;
use Graze\Queue\Client;
use Graze\Queue\Adapter\SqsAdapter;

require __DIR__ . '/../vendor/autoload.php';

$client = new Client(new SqsAdapter(SqsClient::factory([
    'key'    => 'ive_got_the_key',
    'secret' => 'ive_got_the_secret',
    'region' => 'us-east-1'
]), 'urban_cookies'));

// Producer
$client->send([$client->create('foo')]);

// Consumer
$client->receive(function ($msg) {
    var_dump($msg->getBody());
    var_dump($msg->getMetadata()->getAll());
});
