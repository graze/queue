<?php
namespace Graze\Queue\AcknowledgePolicy;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class EagerAcknowledgePolicyTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = m::mock('Graze\Queue\Adapter\AdapterInterface');
        $this->message = m::mock('Graze\Queue\Message\MessageInterface');

        $this->policy = new EagerAcknowledgePolicy();
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Queue\AcknowledgePolicy\AcknowledgePolicyInterface', $this->policy);
    }

    public function testAcknowledge()
    {
        $this->adapter->shouldReceive('acknowledge')->once()->with([$this->message]);

        $this->policy->acknowledge($this->message, $this->adapter);
    }

    public function testAcknowledgeWithResult()
    {
        $this->adapter->shouldReceive('acknowledge')->once()->with([$this->message]);

        $this->policy->acknowledge($this->message, $this->adapter, 'foo');
    }

    public function testFlush()
    {
        $this->policy->flush($this->adapter);
    }
}
