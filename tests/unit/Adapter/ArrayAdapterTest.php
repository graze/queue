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

namespace Graze\Queue\Adapter;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class ArrayAdapterTest extends TestCase
{
    public function setUp()
    {
        $this->factory = m::mock('Graze\Queue\Message\MessageFactoryInterface');

        $this->messageA = $a = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageB = $b = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageC = $c = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messages = [$a, $b, $c];

        $this->adapter = new ArrayAdapter($this->messages);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Queue\Adapter\AdapterInterface', $this->adapter);
    }

    public function testAcknowledge()
    {
        $this->adapter->acknowledge($this->messages);

        $this->assertEquals([], iterator_to_array($this->adapter->dequeue($this->factory, 10)));
    }

    public function testAcknowledgeOne()
    {
        $this->adapter->acknowledge([$this->messageB]);

        $this->assertEquals(
            [$this->messageA, $this->messageC],
            iterator_to_array($this->adapter->dequeue($this->factory, 10))
        );
    }

    public function testDequeue()
    {
        $this->assertEquals($this->messages, iterator_to_array($this->adapter->dequeue($this->factory, 10)));
    }

    public function testDequeueWithLimit()
    {
        $this->assertEquals([$this->messageA], iterator_to_array($this->adapter->dequeue($this->factory, 1)));
    }

    public function testDequeueWithPollingLimit()
    {
        $this->assertEquals($this->messages, iterator_to_array($this->adapter->dequeue($this->factory, null)));
    }

    public function testEnqueue()
    {
        $messageA = m::mock('Graze\Queue\Message\MessageInterface');
        $messageB = m::mock('Graze\Queue\Message\MessageInterface');
        $messageC = m::mock('Graze\Queue\Message\MessageInterface');
        $messages = [$messageA, $messageB, $messageC];
        $merged = array_merge($this->messages, $messages);

        $this->adapter->enqueue($messages);

        $this->assertEquals($merged, iterator_to_array($this->adapter->dequeue($this->factory, 10)));
    }
}
