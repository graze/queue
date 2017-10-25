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

namespace Graze\Queue;

use Aws\ResultInterface;
use Aws\Firehose\FirehoseClient;
use Graze\Queue\Adapter\Exception\FailedEnqueueException;
use Graze\Queue\Adapter\FirehoseAdapter;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;

class FirehoseIntegrationTest extends TestCase
{
    /** @var string */
    private $deliveryStreamName;
    /** @var FirehoseClient|MockInterface */
    private $firehoseClient;
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->deliveryStreamName = 'delivery_stream_foo';
        $this->firehoseClient = m::mock(FirehoseClient::class);
        $this->client = new Client(new FirehoseAdapter($this->firehoseClient, 'delivery_stream_foo'));
    }

    public function testSend()
    {
        $model = m::mock(ResultInterface::class);
        $model->shouldReceive('get')->once()->with('RequestResponses')->andReturn([]);

        $this->firehoseClient->shouldReceive('putRecordBatch')->once()->with([
            'DeliveryStreamName' => $this->deliveryStreamName,
            'Records' => [
                ['Data' => 'foo']
            ]
        ])->andReturn($model);

        $this->client->send([$this->client->create('foo')]);
    }

    /**
     * @expectedException \Graze\Queue\Adapter\Exception\FailedEnqueueException
     */
    public function testSendError()
    {
        $model = m::mock(ResultInterface::class);
        $model->shouldReceive('get')->once()->with('RequestResponses')->andReturn([
            [
                'ErrorCode' => 'fooError',
                'ErrorMessage' => 'Some error message',
                'RecordId' => 'foo',
            ]
        ]);

        $this->firehoseClient->shouldReceive('putRecordBatch')->once()->with([
            'DeliveryStreamName' => $this->deliveryStreamName,
            'Records' => [
                ['Data' => 'foo'],
            ],
        ])->andReturn($model);

        $this->client->send([$this->client->create('foo')]);
    }
}
