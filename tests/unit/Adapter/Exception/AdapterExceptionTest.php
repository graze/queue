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

namespace Graze\Queue\Adapter\Exception;

use Exception;
use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Adapter\NamedInterface;
use Graze\Queue\Message\MessageInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;

class AdapterExceptionTest extends TestCase
{
    /** @var string */
    private $queueName;
    /** @var array */
    private $debug;
    /** @var AdapterInterface|NamedInterface|MockInterface */
    private $adapter;
    /** @var MessageInterface[]|MockInterface[] */
    private $messages;
    /** @var Exception */
    private $previous;
    /** @var AdapterException */
    private $exception;

    public function setUp()
    {
        $this->queueName = 'foobar';
        $this->debug = ['foo' => 'bar'];

        $this->adapter = m::mock(AdapterInterface::class, NamedInterface::class);
        $this->adapter->shouldReceive('getQueueName')->andReturn($this->queueName);

        $a = m::mock('Graze\Queue\Message\MessageInterface');
        $b = m::mock('Graze\Queue\Message\MessageInterface');
        $c = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messages = [$a, $b, $c];

        $this->previous = new Exception();

        $this->exception = new AdapterException('foo', $this->adapter, $this->messages, $this->debug, $this->previous);
    }

    public function testInterface()
    {
        assertThat($this->exception, is(anInstanceOf('RuntimeException')));
    }

    public function testGetAdapter()
    {
        assertThat($this->exception->getAdapter(), is(identicalTo($this->adapter)));
    }

    public function testGetDebug()
    {
        assertThat($this->exception->getDebug(), is(identicalTo($this->debug)));
    }

    public function testGetMessages()
    {
        assertThat($this->exception->getMessages(), is(identicalTo($this->messages)));
    }

    public function testGetPrevious()
    {
        assertThat($this->exception->getPrevious(), is(identicalTo($this->previous)));
    }

    public function testGetQueueName()
    {
        assertThat($this->exception->getQueueName(), is(identicalTo($this->queueName)));
    }
}
