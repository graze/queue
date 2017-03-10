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
 * @link https://github.com/graze/queue
 */

namespace Graze\Queue\Adapter\Exception;

use Exception;
use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class FailedEnqueueExceptionTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = m::mock('Graze\Queue\Adapter\AdapterInterface');
        $this->queueName = 'foobar';
        $this->debug = ['foo' => 'bar'];

        $this->messageA = $a = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageB = $b = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageC = $c = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messages = [$a, $b, $c];

        $this->previous = new Exception();

        $this->exception = new FailedEnqueueException($this->adapter, $this->messages, $this->queueName, $this->debug, $this->previous);
    }

    public function testInterface()
    {
        assertThat($this->exception, is(anInstanceOf('Graze\Queue\Adapter\Exception\AdapterException')));
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
