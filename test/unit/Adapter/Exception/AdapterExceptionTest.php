<?php
namespace Graze\Queue\Adapter\Exception;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class AdapterExceptionTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = m::mock('Graze\Queue\Adapter\AdapterInterface');
        $this->debug = ['foo' => 'bar'];

        $this->messageA = $a = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageB = $b = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageC = $c = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messages = [$a, $b, $c];

        $this->exception = new AdapterException('foo', $this->adapter, $this->messages, $this->debug);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('RuntimeException', $this->exception);
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
