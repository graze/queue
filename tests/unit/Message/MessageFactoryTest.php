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

use Graze\Queue\Test\TestCase;

class MessageFactoryTest extends TestCase
{
    /** @var MessageFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new MessageFactory();
    }

    public function testInterface()
    {
        assertThat($this->factory, is(anInstanceOf('Graze\Queue\Message\MessageFactoryInterface')));
    }

    public function testCreateMessage()
    {
        $message = $this->factory->createMessage('foo');

        assertThat($message, is(anInstanceOf('Graze\Queue\Message\MessageInterface')));
        assertThat($message->getBody(), is(identicalTo('foo')));
        assertThat($message->isValid(), is(identicalTo(true)));
    }

    public function testCreateMessageWithMetadata()
    {
        $message = $this->factory->createMessage('foo', ['metadata' => ['bar' => 'baz']]);

        assertThat($message, is(anInstanceOf('Graze\Queue\Message\MessageInterface')));
        assertThat($message->getBody(), is(identicalTo('foo')));
        assertThat($message->getMetadata()->get('bar'), is(identicalTo('baz')));
    }

    public function testCreateMessageWithValidator()
    {
        $message = $this->factory->createMessage('bar', [
            'validator' => function ($msg) {
                return false;
            },
        ]);

        assertThat($message, is(anInstanceOf('Graze\Queue\Message\MessageInterface')));
        assertThat($message->getBody(), is(identicalTo('bar')));
        assertThat($message->isValid(), is(identicalTo(false)));
    }
}
