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
 * @link https://github.com/graze/queue
 */

namespace Graze\Queue;

use Graze\Queue\Adapter\ArrayAdapter;
use Graze\Queue\Message\MessageFactory;
use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class ArrayIntegrationTest extends TestCase
{
    public function setUp()
    {
        $factory = new MessageFactory();

        $this->messages = [
            $factory->createMessage('foo'),
            $factory->createMessage('bar'),
            $factory->createMessage('baz')
        ];

        $this->client = new Client(new ArrayAdapter($this->messages));
    }

    public function testReceive()
    {
        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, 100);

        $this->assertEquals($this->messages, $msgs);
    }

    public function testReceiveWithPolling()
    {
        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, null);

        $this->assertEquals($this->messages, $msgs);
    }

    public function testSend()
    {
        $this->client->send([$this->client->create('foo')]);
    }
}
