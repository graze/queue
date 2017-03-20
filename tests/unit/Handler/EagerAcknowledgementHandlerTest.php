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

namespace Graze\Queue\Handler;

use ArrayIterator;
use Closure;
use Graze\Queue\Adapter\AdapterInterface;
use GuzzleHttp\Message\MessageInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;

class EagerAcknowledgementHandlerTest extends TestCase
{
    /** @var AdapterInterface|MockInterface */
    private $adapter;
    /** @var MessageInterface|MockInterface */
    private $messageA;
    /** @var MessageInterface|MockInterface */
    private $messageB;
    /** @var MessageInterface|MockInterface */
    private $messageC;
    /** @var ArrayIterator */
    private $messages;
    /** @var EagerAcknowledgementHandler */
    private $handler;

    public function setUp()
    {
        $this->adapter = m::mock(AdapterInterface::class);

        $this->messageA = $a = m::mock(MessageInterface::class);
        $this->messageB = $b = m::mock(MessageInterface::class);
        $this->messageC = $c = m::mock(MessageInterface::class);
        $this->messages = new ArrayIterator([$a, $b, $c]);

        $this->handler = new EagerAcknowledgementHandler();
    }

    public function testHandle()
    {
        $handler = $this->handler;

        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);

        // @see https://github.com/padraic/mockery/issues/331
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageA]));
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageB]));
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageC]));

        $msgs = [];
        $handler($this->messages, $this->adapter, function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        });

        assertThat($msgs, is(identicalTo(iterator_to_array($this->messages))));
    }

    public function testHandleInvalidMessage()
    {
        $handler = $this->handler;

        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(false);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);

        // @see https://github.com/padraic/mockery/issues/331
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageA]));
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageC]));

        $msgs = [];
        $handler($this->messages, $this->adapter, function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        });

        assertThat($msgs, is(identicalTo([$this->messageA, $this->messageC])));
    }

    public function testHandleWorkerWithThrownException()
    {
        $handler = $this->handler;

        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);

        // @see https://github.com/padraic/mockery/issues/331
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageA]));

        $this->setExpectedException('RuntimeException', 'foo');

        $handler($this->messages, $this->adapter, function ($msg) {
            if ($msg === $this->messageB) {
                throw new RuntimeException('foo');
            }
        });
    }
}
