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
        assertThat($this->adapter, is(anInstanceOf('Graze\Queue\Adapter\AdapterInterface')));
    }

    public function testAcknowledge()
    {
        $this->adapter->acknowledge($this->messages);

        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(identicalTo([])));
    }

    public function testAcknowledgeOne()
    {
        $this->adapter->acknowledge([$this->messageB]);

        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(identicalTo([$this->messageA, $this->messageC])));
    }

    public function testDequeue()
    {
        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(identicalTo($this->messages)));
    }

    public function testDequeueWithLimit()
    {
        $iterator = $this->adapter->dequeue($this->factory, 1);

        assertThat(iterator_to_array($iterator), is(identicalTo([$this->messageA])));
    }

    public function testDequeueWithPollingLimit()
    {
        $iterator = $this->adapter->dequeue($this->factory, null);

        assertThat(iterator_to_array($iterator), is(identicalTo($this->messages)));
    }

    public function testDequeueWithNoMessages()
    {
        $adapter = new ArrayAdapter();

        $iterator = $adapter->dequeue($this->factory, null);

        assertThat(iterator_to_array($iterator), is(emptyArray()));
    }

    public function testDequeueWithLimitAndNoMessages()
    {
        $adapter = new ArrayAdapter();

        $iterator = $adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(emptyArray()));
    }

    public function testEnqueue()
    {
        $messageA = m::mock('Graze\Queue\Message\MessageInterface');
        $messageB = m::mock('Graze\Queue\Message\MessageInterface');
        $messageC = m::mock('Graze\Queue\Message\MessageInterface');
        $messages = [$messageA, $messageB, $messageC];
        $merged = array_merge($this->messages, $messages);

        $this->adapter->enqueue($messages);

        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(identicalTo($merged)));
    }
}
