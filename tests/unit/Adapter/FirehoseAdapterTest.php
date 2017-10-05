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

namespace Graze\Queue\Adapter;

use Aws\ResultInterface;
use Aws\Firehose\FirehoseClient;
use Graze\DataStructure\Container\ContainerInterface;
use Graze\Queue\Adapter\Exception\MethodNotSupportedException;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;

class FirehoseAdapterTest extends TestCase
{
    /** @var MessageInterface|MockInterface */
    private $messageA;
    /** @var MessageInterface|MockInterface */
    private $messageB;
    /** @var MessageInterface|MockInterface */
    private $messageC;
    /** @var MessageInterface[]|MockInterface[] */
    private $messages;
    /** @var ResultInterface|MockInterface */
    private $model;
    /** @var MessageFactoryInterface|MockInterface */
    private $factory;
    /** @var FirehoseClient */
    private $client;

    public function setUp()
    {
        $this->client = m::mock(FirehoseClient::class);
        $this->model = m::mock(ResultInterface::class);
        $this->factory = m::mock(MessageFactoryInterface::class);

        $this->messageA = $a = m::mock(MessageInterface::class);
        $this->messageB = $b = m::mock(MessageInterface::class);
        $this->messageC = $c = m::mock(MessageInterface::class);
        $this->messages = [$a, $b, $c];
    }

    public function testInterface()
    {
        assertThat(new FirehoseAdapter($this->client, 'foo'), is(anInstanceOf('Graze\Queue\Adapter\AdapterInterface')));
    }

    public function testEnqueue()
    {
        $adapter = new FirehoseAdapter($this->client, 'foo');

        $metadata = m::mock(ContainerInterface::class);
        $metadata->shouldReceive('get')
                 ->with('MessageAttributes')
                 ->times(3)
                 ->andReturn(null);

        $this->messageA->shouldReceive('getBody')->once()->withNoArgs()->andReturn('foo');
        $this->messageB->shouldReceive('getBody')->once()->withNoArgs()->andReturn('bar');
        $this->messageC->shouldReceive('getBody')->once()->withNoArgs()->andReturn('baz');
        $this->messageA->shouldReceive('getMetadata')->andReturn($metadata);
        $this->messageB->shouldReceive('getMetadata')->andReturn($metadata);
        $this->messageC->shouldReceive('getMetadata')->andReturn($metadata);

        $this->model->shouldReceive('get')->once()->with('RequestResponses')->andReturn([]);

        $this->client->shouldReceive('putRecordBatch')->once()->with([
            'DeliveryStreamName' => 'foo',
            'Records' => [
                ['Data' => json_encode(['Id' => 0, 'MessageBody' => 'foo', 'MessageAttributes' => []])],
                ['Data' => json_encode(['Id' => 1, 'MessageBody' => 'bar', 'MessageAttributes' => []])],
                ['Data' => json_encode(['Id' => 2, 'MessageBody' => 'baz', 'MessageAttributes' => []])]
            ],
        ])->andReturn($this->model);

        $adapter->enqueue($this->messages);
    }

    /**
     * @expectedException \Graze\Queue\Adapter\Exception\MethodNotSupportedException
     */
    public function testAcknowledge()
    {
        $adapter = new FirehoseAdapter($this->client, 'foo');
        $adapter->acknowledge($this->messages);
    }

    /**
     * @expectedException \Graze\Queue\Adapter\Exception\MethodNotSupportedException
     */
    public function testDequeue()
    {
        $adapter = new FirehoseAdapter($this->client, 'foo');
        $adapter->dequeue($this->factory, 10);
    }

    /**
     * @expectedException \Graze\Queue\Adapter\Exception\MethodNotSupportedException
     */
    public function testPurge()
    {
        $adapter = new FirehoseAdapter($this->client, 'foo');
        $adapter->purge();
    }

    /**
     * @expectedException \Graze\Queue\Adapter\Exception\MethodNotSupportedException
     */
    public function testDelete()
    {
        $adapter = new FirehoseAdapter($this->client, 'foo');
        $adapter->delete();
    }
}
