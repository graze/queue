<?php

/*
 * This file is part of Graze Queue
 *
 * Copyright (c) 2014 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see  http://github.com/graze/queue/blob/master/LICENSE
 * @link http://github.com/graze/queue
 */

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
