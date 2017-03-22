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

namespace Graze\Queue\Message;

use Graze\DataStructure\Container\ContainerInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;

class MessageTest extends TestCase
{
    /** @var ContainerInterface|MockInterface */
    private $metadata;

    public function setUp()
    {
        $this->metadata = m::mock(ContainerInterface::class);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            MessageInterface::class,
            new Message('foo', $this->metadata, function () {
            })
        );
    }

    public function testGetBody()
    {
        $message = new Message('foo', $this->metadata, function () {
        });

        assertThat($message->getBody(), is(identicalTo('foo')));
    }

    public function testGetMetadata()
    {
        $message = new Message('foo', $this->metadata, function () {
        });

        assertThat($message->getMetadata(), is(identicalTo($this->metadata)));
    }

    public function testIsValidIsFalse()
    {
        $message = new Message('foo', $this->metadata, function () {
            return false;
        });

        assertThat($message->isValid(), is(identicalTo(false)));
    }

    public function testIsValidIsTrue()
    {
        $message = new Message('foo', $this->metadata, function () {
            return true;
        });

        assertThat($message->isValid(), is(identicalTo(true)));
    }

    public function testIsValidIsCalledWithMessage()
    {
        $seen = null;
        $message = new Message('foo', $this->metadata, function ($msg) use (&$seen) {
            $seen = $msg;
        });

        $message->isValid();

        assertThat($seen, is(identicalTo($message)));
    }
}
