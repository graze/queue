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

namespace Graze\Queue\Message;

use PHPUnit_Framework_TestCase as TestCase;

class MessageFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->factory = new MessageFactory();
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Queue\Message\MessageFactoryInterface', $this->factory);
    }

    public function testCreateMessage()
    {
        $message = $this->factory->createMessage('foo');

        $this->assertInstanceOf('Graze\Queue\Message\MessageInterface', $message);
        $this->assertEquals('foo', $message->getBody());
        $this->assertTrue($message->isValid());
    }

    public function testCreateMessageWithMetadata()
    {
        $message = $this->factory->createMessage('foo', ['metadata' => ['bar'=>'baz']]);

        $this->assertInstanceOf('Graze\Queue\Message\MessageInterface', $message);
        $this->assertEquals('foo', $message->getBody());
        $this->assertEquals('baz', $message->getMetadata()->get('bar'));
    }

    public function testCreateMessageWithValidator()
    {
        $message = $this->factory->createMessage('bar', ['validator' => function ($msg) {
            return false;
        }]);

        $this->assertInstanceOf('Graze\Queue\Message\MessageInterface', $message);
        $this->assertEquals('bar', $message->getBody());
        $this->assertFalse($message->isValid());
    }
}
