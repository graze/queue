<?php

/**
 * This file is part of graze/queue.
 *
 * Copyright (c) 2015 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license http://github.com/graze/queue/blob/master/LICENSE MIT
 * @link http://github.com/graze/queue
 */

namespace Graze\Queue\Adapter\Exception;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class FailedAcknowledgementExceptionTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = m::mock('Graze\Queue\Adapter\AdapterInterface');
        $this->debug = ['foo' => 'bar'];

        $this->messageA = $a = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageB = $b = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageC = $c = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messages = [$a, $b, $c];

        $this->exception = new FailedAcknowledgementException($this->adapter, $this->messages, $this->debug);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Queue\Adapter\Exception\AdapterException', $this->exception);
    }

    public function testGetAdapter()
    {
        $this->assertSame($this->adapter, $this->exception->getAdapter());
    }

    public function testGetDebug()
    {
        $this->assertSame($this->debug, $this->exception->getDebug());
    }

    public function testGetMessages()
    {
        $this->assertSame($this->messages, $this->exception->getMessages());
    }
}
