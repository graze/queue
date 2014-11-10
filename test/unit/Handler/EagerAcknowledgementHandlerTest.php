<?php
namespace Graze\Queue\Handler;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;

class EagerAcknowledgementHandlerTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = m::mock('Graze\Queue\Adapter\AdapterInterface');

        $this->messageA = $a = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageB = $b = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageC = $c = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messages = [$a, $b, $c];

        $this->handler = new EagerAcknowledgementHandler();
    }

    public function testHandle()
    {
        $handler = $this->handler;

        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);

        // @see https://github.com/padraic/mockery/issues/331
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageA]));
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageB]));
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageC]));

        $msgs = [];
        $adps = [];
        $handler($this->messages, $this->adapter, function ($msg, $adapter) use (&$msgs, &$adps) {
            $msgs[] = $msg;
            $adps[] = $adapter;
        });

        $this->assertEquals($this->messages, $msgs);
        $this->assertEquals([$this->adapter, $this->adapter, $this->adapter], $adps);
    }

    public function testHandleInvalidMessage()
    {
        $handler = $this->handler;

        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(false);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);

        // @see https://github.com/padraic/mockery/issues/331
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageA]));
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageC]));

        $msgs = [];
        $adps = [];
        $handler($this->messages, $this->adapter, function ($msg, $adapter) use (&$msgs, &$adps) {
            $msgs[] = $msg;
            $adps[] = $adapter;
        });

        $this->assertEquals([$this->messageA, $this->messageC], $msgs);
        $this->assertEquals([$this->adapter, $this->adapter], $adps);
    }

    public function testHandleWorkerWithThrownException()
    {
        $handler = $this->handler;

        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);

        // @see https://github.com/padraic/mockery/issues/331
        $this->adapter->shouldReceive('acknowledge')->once()->with(m::mustBe([$this->messageA]));

        $this->setExpectedException('RuntimeException', 'foo');
        $handler($this->messages, $this->adapter, function ($msg) {
            if ($msg === $this->messageB) {
                throw new RuntimeException('foo');
            }
        });
    }
}
