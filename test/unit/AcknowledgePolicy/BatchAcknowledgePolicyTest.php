<?php
namespace Graze\Queue\AcknowledgePolicy;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class BatchAcknowledgePolicyTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = m::mock('Graze\Queue\Adapter\AdapterInterface');
        $this->message = m::mock('Graze\Queue\Message\MessageInterface');

        $this->policy = new BatchAcknowledgePolicy(3);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Queue\AcknowledgePolicy\AcknowledgePolicyInterface', $this->policy);
    }

    public function testAcknowledge()
    {
        $this->adapter->shouldReceive('acknowledge')->never();

        $this->policy->acknowledge($this->message, $this->adapter);
    }

    public function testAcknowledgeWithResult()
    {
        $this->adapter->shouldReceive('acknowledge')->never();

        $this->policy->acknowledge($this->message, $this->adapter, 'foo');
    }

    public function testAcknowledgeExceedsBatchSize()
    {
        $messageA = m::mock('Graze\Queue\Message\MessageInterface');
        $messageB = m::mock('Graze\Queue\Message\MessageInterface');
        $messages = [$this->message, $messageA, $messageB];

        $this->adapter->shouldReceive('acknowledge')->once()->with($messages);

        $this->policy->acknowledge($this->message, $this->adapter);
        $this->policy->acknowledge($messageA, $this->adapter);
        $this->policy->acknowledge($messageB, $this->adapter);
    }

    public function testFlush()
    {
        $this->adapter->shouldReceive('acknowledge')->once()->with([$this->message]);

        $this->policy->acknowledge($this->message, $this->adapter);
        $this->policy->flush($this->adapter);
    }

    public function testFlushWithNoMessages()
    {
        $this->adapter->shouldReceive('acknowledge')->never();

        $this->policy->flush($this->adapter);
    }
}
