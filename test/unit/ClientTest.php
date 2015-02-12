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

namespace Graze\Queue;

use ArrayIterator;
use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;

class ClientTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = m::mock('Graze\Queue\Adapter\AdapterInterface');
        $this->factory = m::mock('Graze\Queue\Message\MessageFactoryInterface');
        $this->handler = m::mock('Graze\Queue\Handler\AbstractAcknowledgementHandler');

        $this->messageA = $a = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageB = $b = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageC = $c = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messages = [$a, $b, $c];

        $this->client = new Client($this->adapter, [
            'handler' => $this->handler,
            'message_factory' => $this->factory
        ]);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Queue\ConsumerInterface', $this->client);
        $this->assertInstanceOf('Graze\Queue\ProducerInterface', $this->client);
    }

    public function testCreate()
    {
        $this->factory->shouldReceive('createMessage')->once()->with('foo', ['bar'])->andReturn($this->messageA);

        $this->assertSame($this->messageA, $this->client->create('foo', ['bar']));
    }

    public function testSend()
    {
        $this->adapter->shouldReceive('enqueue')->once()->with($this->messages);

        $this->client->send($this->messages);
    }

    public function testReceive()
    {
        $worker = function() {
        };

        $messages = new ArrayIterator($this->messages);

        $this->adapter->shouldReceive('dequeue')->once()->with($this->factory, 1)->andReturn($messages);
        $this->handler->shouldReceive('__invoke')->once()->with($messages, $this->adapter, $worker);

        $this->client->receive($worker);
    }
}
