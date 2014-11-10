<?php
namespace Graze\Queue\Message;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class MessageTest extends TestCase
{
    public function setUp()
    {
        $this->metadata = m::mock('Graze\DataStructure\Container\ContainerInterface');
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Queue\Message\MessageInterface', new Message('foo', $this->metadata, function(){}));
    }

    public function testGetBody()
    {
        $message = new Message('foo', $this->metadata, function(){});

        $this->assertSame('foo', $message->getBody());
    }

    public function testGetMetadata()
    {
        $message = new Message('foo', $this->metadata, function(){});

        $this->assertSame($this->metadata, $message->getMetadata());
    }

    public function testIsValidIsFalse()
    {
        $message = new Message('foo', $this->metadata, function () {
            return false;
        });

        $this->assertFalse($message->isValid());
    }

    public function testIsValidIsTrue()
    {
        $message = new Message('foo', $this->metadata, function () {
            return true;
        });

        $this->assertTrue($message->isValid());
    }

    public function testIsValidIsCalledWithMessage()
    {
        $seen = null;
        $message = new Message('foo', $this->metadata, function ($msg) use (&$seen) {
            $seen = $msg;
        });

        $message->isValid();
        $this->assertSame($message, $seen);
    }
}
