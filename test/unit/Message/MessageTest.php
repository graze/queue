<?php
namespace Graze\Queue\Message;

use PHPUnit_Framework_TestCase as TestCase;

class MessageTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Queue\Message\MessageInterface', new Message('foo', [], function(){}));
    }

    public function testGetBody()
    {
        $message = new Message('foo', [], function(){});

        $this->assertSame('foo', $message->getBody());
    }

    public function testGetMetadata()
    {
        $message = new Message('foo', ['bar'], function(){});

        $this->assertSame(['bar'], $message->getMetadata());
    }

    public function testIsValidIsFalse()
    {
        $message = new Message('foo', [], function () {
            return false;
        });

        $this->assertFalse($message->isValid());
    }

    public function testIsValidIsTrue()
    {
        $message = new Message('foo', [], function () {
            return true;
        });

        $this->assertTrue($message->isValid());
    }

    public function testIsValidIsCalledWithMessage()
    {
        $seen = null;
        $message = new Message('foo', [], function ($msg) use (&$seen) {
            $seen = $msg;
        });

        $message->isValid();
        $this->assertSame($message, $seen);
    }
}
