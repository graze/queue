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

use ArrayIterator;
use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Handler\AbstractAcknowledgementHandler;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use Mockery as m;
use Mockery\MockInterface;
use Graze\Queue\Test\TestCase;

class ClientTest extends TestCase
{
    /** @var AdapterInterface|MockInterface */
    private $adapter;
    /** @var MessageFactoryInterface|MockInterface */
    private $factory;
    /** @var AbstractAcknowledgementHandler|MockInterface */
    private $handler;
    /** @var MessageInterface|MockInterface */
    private $messageA;
    /** @var MessageInterface|MockInterface */
    private $messageB;
    /** @var MessageInterface|MockInterface */
    private $messageC;
    /** @var MessageInterface[]|MockInterface[] */
    private $messages;
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->adapter = m::mock(AdapterInterface::class);
        $this->factory = m::mock(MessageFactoryInterface::class);
        $this->handler = m::mock(AbstractAcknowledgementHandler::class);

        $this->messageA = $a = m::mock(MessageInterface::class);
        $this->messageB = $b = m::mock(MessageInterface::class);
        $this->messageC = $c = m::mock(MessageInterface::class);
        $this->messages = [$a, $b, $c];

        $this->client = new Client($this->adapter, [
            'handler'         => $this->handler,
            'message_factory' => $this->factory,
        ]);
    }

    public function testInterface()
    {
        assertThat($this->client, is(anInstanceOf(ConsumerInterface::class)));
        assertThat($this->client, is(anInstanceOf(DeleterInterface::class)));
        assertThat($this->client, is(anInstanceOf(ProducerInterface::class)));
        assertThat($this->client, is(anInstanceOf(PurgerInterface::class)));
    }

    public function testCreate()
    {
        $this->factory->shouldReceive('createMessage')->once()->with('foo', ['bar'])->andReturn($this->messageA);

        assertThat($this->client->create('foo', ['bar']), is(identicalTo($this->messageA)));
    }

    public function testSend()
    {
        $this->adapter->shouldReceive('enqueue')->once()->with($this->messages);

        $this->client->send($this->messages);
    }

    public function testReceive()
    {
        $worker = function () {
        };

        $messages = new ArrayIterator($this->messages);

        $this->adapter->shouldReceive('dequeue')->once()->with($this->factory, 1)->andReturn($messages);
        $this->handler->shouldReceive('__invoke')->once()->with($messages, $this->adapter, $worker);

        $this->client->receive($worker);
    }

    public function testPurge()
    {
        $this->adapter->shouldReceive('purge')->once();
        $this->client->purge();
    }

    public function testDelete()
    {
        $this->adapter->shouldReceive('delete')->once();
        $this->client->delete();
    }
}
