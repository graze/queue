<?php
namespace Graze\Queue;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;

class ClientTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = m::mock('Graze\Queue\Adapter\AdapterInterface');
        $this->factory = m::mock('Graze\Queue\Message\MessageFactoryInterface');
        $this->policy  = m::mock('Graze\Queue\AcknowledgePolicy\AcknowledgePolicyInterface');

        $this->messageA = $a = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageB = $b = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messageC = $c = m::mock('Graze\Queue\Message\MessageInterface');
        $this->messages = [$a, $b, $c];

        $this->client = new Client($this->adapter, $this->policy, $this->factory);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Queue\ConsumerInterface', $this->client);
        $this->assertInstanceOf('Graze\Queue\ProducerInterface', $this->client);
    }

    public function testCreate()
    {
        $this->factory->shouldReceive('createMessage')->once()->with('foo', ['bar'])->andReturn($this->messageA);

        $this->assertSame($this->messageA, $this->client->create('foo', ['bar']));
    }

    public function testSend()
    {
        $this->adapter->shouldReceive('enqueue')->once()->with($this->messages);

        $this->client->send($this->messages);
    }

    public function testReceive()
    {
        $this->adapter->shouldReceive('dequeue')->once()->with($this->factory, 1)->andReturn($this->messages);
        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->policy->shouldReceive('acknowledge')->once()->with($this->messageA, $this->adapter, null);
        $this->policy->shouldReceive('acknowledge')->once()->with($this->messageB, $this->adapter, null);
        $this->policy->shouldReceive('acknowledge')->once()->with($this->messageC, $this->adapter, null);
        $this->policy->shouldReceive('flush')->once()->with($this->adapter);

        $msgs = [];
        $adps = [];
        $this->client->receive(function ($msg, $adapter) use (&$msgs, &$adps) {
            $msgs[] = $msg;
            $adps[] = $adapter;
        });

        $this->assertEquals($this->messages, $msgs);
        $this->assertEquals([$this->adapter, $this->adapter, $this->adapter], $adps);
    }

    public function testReceiveWorkerWithReturnValue()
    {
        $this->adapter->shouldReceive('dequeue')->once()->with($this->factory, 1)->andReturn($this->messages);
        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->policy->shouldReceive('acknowledge')->once()->with($this->messageA, $this->adapter, 1);
        $this->policy->shouldReceive('acknowledge')->once()->with($this->messageB, $this->adapter, 2);
        $this->policy->shouldReceive('acknowledge')->once()->with($this->messageC, $this->adapter, 3);
        $this->policy->shouldReceive('flush')->once()->with($this->adapter);

        $count = 0;
        $this->client->receive(function () use (&$count) {
            return ++$count;
        });

        $this->assertEquals(3, $count);
    }

    public function testReceiveWorkerWithInvalidMessage()
    {
        $this->adapter->shouldReceive('dequeue')->once()->with($this->factory, 1)->andReturn($this->messages);
        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(false);
        $this->messageC->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->policy->shouldReceive('acknowledge')->once()->with($this->messageA, $this->adapter, null);
        $this->policy->shouldReceive('acknowledge')->once()->with($this->messageC, $this->adapter, null);
        $this->policy->shouldReceive('flush')->once()->with($this->adapter);

        $seen = [];
        $this->client->receive(function ($msg) use (&$seen) {
            $seen[] = $msg;
        });

        $this->assertEquals([$this->messageA, $this->messageC], $seen);
    }

    public function testReceiveWorkerWithThrownException()
    {
        $this->adapter->shouldReceive('dequeue')->once()->with($this->factory, 1)->andReturn($this->messages);
        $this->messageA->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->messageB->shouldReceive('isValid')->once()->withNoArgs()->andReturn(true);
        $this->policy->shouldReceive('acknowledge')->once()->with($this->messageA, $this->adapter, null);
        $this->policy->shouldReceive('flush')->once()->with($this->adapter);

        $this->setExpectedException('RuntimeException');
        $this->client->receive(function ($msg) {
            if ($msg === $this->messageB) {
                throw new RuntimeException('foo');
            }
        });
    }
}
