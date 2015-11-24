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
use Graze\Queue\Message\MessageInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;

class ResultAcknowledgementHandlerTest extends TestCase
{
    /**
     * @var AdapterInterface|MockInterface
     */
    private $adapter;

    /**
     * @var AbstractAcknowledgementHandler|MockInterface
     */
    private $passThrough;

    /**
     * @var ResultAcknowledgementHandler
     */
    private $handler;

    /**
     * @var MessageInterface|MockInterface
     */
    private $message;

    /**
     * @var ArrayIterator
     */
    private $messages;

    public function setUp()
    {
        $this->adapter = m::mock('Graze\Queue\Adapter\AdapterInterface');

        $this->message = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messages = new ArrayIterator([$this->message]);

        $this->handler = new EagerAcknowledgementHandler();
    }

    public function testHandleTrueResult()
    {
        $handler = new ResultAcknowledgementHandler(function ($result) {
            return $result === true;
        }, $this->handler);

        $this->message->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->message]));

        $msgs = [];
        $handler($this->messages, $this->adapter, function ($msg, Closure $done) use (&$msgs) {
            $msgs[] = $msg;
            return true;
        });

        assertThat($msgs, is(identicalTo(iterator_to_array($this->messages))));
    }

    public function testHandleNonTrueResponse()
    {
        $handler = new ResultAcknowledgementHandler(function ($result) {
            return $result === true;
        }, $this->handler);

        $this->message->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);

        $handler($this->messages, $this->adapter, function ($msg, Closure $done) use (&$msgs) {
            return false;
        });
    }

    public function testCustomResultAcknowledgementHandler()
    {
        $handler = new ResultAcknowledgementHandler(function ($result) {
            return $result === false;
        }, $this->handler);

        $this->message->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->message]));

        $handler($this->messages, $this->adapter, function ($msg) {
            return false;
        });
    }
}
