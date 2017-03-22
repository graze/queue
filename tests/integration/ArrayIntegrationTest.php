<?php

/**
 * This file is part of graze/queue.
 *
 * Copyright (c) 2015 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/queue/blob/master/LICENSE MIT
 *
 * @link    https://github.com/graze/queue
 */

namespace Graze\Queue;

use Graze\Queue\Adapter\ArrayAdapter;
use Graze\Queue\Message\MessageFactory;
use Graze\Queue\Message\MessageInterface;
use PHPUnit_Framework_TestCase as TestCase;

class ArrayIntegrationTest extends TestCase
{
    /** @var MessageInterface[] */
    private $messages;
    /** @var Client */
    private $client;

    public function setUp()
    {
        $factory = new MessageFactory();

        $this->messages = [
            $factory->createMessage('foo'),
            $factory->createMessage('bar'),
            $factory->createMessage('baz'),
        ];

        $this->client = new Client(new ArrayAdapter($this->messages));
    }

    public function testReceive()
    {
        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, 100);

        assertThat($msgs, is(identicalTo($this->messages)));
    }

    public function testReceiveWithPolling()
    {
        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, null);

        assertThat($msgs, is(identicalTo($this->messages)));
    }

    public function testReceiveWithNoMessages()
    {
        $client = new Client(new ArrayAdapter());

        $msgs = [];
        $client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, null);

        assertThat($msgs, is(emptyArray()));
    }

    public function testReceiveWithLimitAndNoMessages()
    {
        $client = new Client(new ArrayAdapter());

        $msgs = [];
        $client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, 10);

        assertThat($msgs, is(emptyArray()));
    }

    public function testSend()
    {
        $this->client->send([$this->client->create('foo')]);
    }

    public function testPurge()
    {
        $this->client->purge();

        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, null);

        assertThat($msgs, is(emptyArray()));
    }

    public function testDelete()
    {
        $this->client->delete();

        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, null);

        assertThat($msgs, is(emptyArray()));
    }
}
