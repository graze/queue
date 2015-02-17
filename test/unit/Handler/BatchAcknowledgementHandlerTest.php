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

namespace Graze\Queue\Handler;

use ArrayIterator;
use Closure;
use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;

class BatchAcknowledgementHandlerTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = m::mock('Graze\Queue\Adapter\AdapterInterface');

        $this->messageA = $a = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageB = $b = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageC = $c = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messages = new ArrayIterator([$a, $b, $c]);

        $this->handler = new BatchAcknowledgementHandler(3);
    }

    public function testHandle()
    {
        $handler = $this->handler;

        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->adapter->shouldReceive('acknowledge')->once()->with(iterator_to_array($this->messages));

        $msgs = [];
        $handler($this->messages, $this->adapter, function ($msg, Closure $done) use (&$msgs) {
            $msgs[] = $msg;
        });

        $this->assertEquals(iterator_to_array($this->messages), $msgs);
    }

    public function testHandleInvalidMessage()
    {
        $handler = $this->handler;

        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(false);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->adapter->shouldReceive('acknowledge')->once()->with([$this->messageA, $this->messageC]);

        $msgs = [];
        $handler($this->messages, $this->adapter, function ($msg, Closure $done) use (&$msgs) {
            $msgs[] = $msg;
        });

        $this->assertEquals([$this->messageA, $this->messageC], $msgs);
    }

    public function testHandleWorkerWithThrownException()
    {
        $handler = $this->handler;

        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->adapter->shouldReceive('acknowledge')->once()->with([$this->messageA]);

        $this->setExpectedException('RuntimeException', 'foo');
        $handler($this->messages, $this->adapter, function ($msg) {
            if ($msg === $this->messageB) {
                throw new RuntimeException('foo');
            }
        });
    }
}
