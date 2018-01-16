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

namespace Graze\Queue\Adapter;

use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;

class ArrayAdapterTest extends TestCase
{
    /** @var MessageFactoryInterface|MockInterface */
    private $factory;
    /** @var MessageInterface|MockInterface */
    private $messageA;
    /** @var MessageInterface|MockInterface */
    private $messageB;
    /** @var MessageInterface|MockInterface */
    private $messageC;
    /** @var MessageInterface[]|MockInterface[] */
    private $messages;
    /** @var ArrayAdapter */
    private $adapter;

    public function setUp()
    {
        $this->factory = m::mock(MessageFactoryInterface::class);

        $this->messageA = $a = m::mock(MessageInterface::class);
        $this->messageB = $b = m::mock(MessageInterface::class);
        $this->messageC = $c = m::mock(MessageInterface::class);
        $this->messages = [$a, $b, $c];

        $this->adapter = new ArrayAdapter($this->messages);
    }

    public function testInterface()
    {
        assertThat($this->adapter, is(anInstanceOf(AdapterInterface::class)));
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

    public function testReject()
    {
        $this->adapter->reject($this->messages);

        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(identicalTo($this->messages)));
    }

    public function testRejectOne()
    {
        $this->adapter->reject([$this->messageA]);

        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(identicalTo($this->messages)));
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
        $messageA = m::mock(MessageInterface::class);
        $messageB = m::mock(MessageInterface::class);
        $messageC = m::mock(MessageInterface::class);
        $messages = [$messageA, $messageB, $messageC];
        $merged = array_merge($this->messages, $messages);

        $this->adapter->enqueue($messages);

        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(identicalTo($merged)));
    }

    public function testPurge()
    {
        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(nonEmptyArray()));

        $this->adapter->purge();

        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(emptyArray()));
    }

    public function testDelete()
    {
        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(nonEmptyArray()));

        $this->adapter->delete();

        $iterator = $this->adapter->dequeue($this->factory, 10);

        assertThat(iterator_to_array($iterator), is(emptyArray()));
    }
}
