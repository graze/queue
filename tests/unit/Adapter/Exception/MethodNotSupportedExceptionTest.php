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
use Graze\Queue\Message\MessageInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;

class MethodNotSupportedExceptionTest extends TestCase
{
    /** @var AdapterInterface|MockInterface */
    private $adapter;
    /** @var array */
    private $debug;
    /** @var MessageInterface[]|MockInterface[] */
    private $messages;
    /** @var Exception */
    private $previous;
    /** @var MethodNotSupportedException */
    private $exception;

    public function setUp()
    {
        $this->adapter = m::mock(AdapterInterface::class);
        $this->debug = ['foo' => 'bar'];

        $a = m::mock(MessageInterface::class);
        $b = m::mock(MessageInterface::class);
        $c = m::mock(MessageInterface::class);
        $this->messages = [$a, $b, $c];

        $this->previous = new Exception();

        $this->exception = new MethodNotSupportedException(
            'method',
            $this->adapter,
            $this->messages,
            $this->debug,
            $this->previous
        );
    }

    public function testInterface()
    {
        assertThat($this->exception, is(anInstanceOf(AdapterException::class)));
    }

    public function testGetMethod()
    {
        assertThat($this->exception->getMethod(), is(identicalTo('method')));
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
}
