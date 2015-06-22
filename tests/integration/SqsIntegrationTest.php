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
 * @link https://github.com/graze/queue
 */

namespace Graze\Queue;

use Graze\Queue\Adapter\SqsAdapter;
use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class SqsIntegrationTest extends TestCase
{
    public function setUp()
    {
        $this->name = 'queue_foo';
        $this->sqsClient = m::mock('Aws\Sqs\SqsClient');
        $this->client = new Client(new SqsAdapter($this->sqsClient, 'queue_foo'));
    }

    protected function stubCreateQueue()
    {
        $url = 'queue://foo';
        $model = m::mock('Guzzle\Service\Resource\Model');
        $model->shouldReceive('getPath')->once()->with('QueueUrl')->andReturn($url);

        $this->sqsClient->shouldReceive('createQueue')->once()->with([
            'QueueName' => $this->name,
            'Attributes' => []
        ])->andReturn($model);

        return $url;
    }

    protected function stubQueueVisibilityTimeout($url)
    {
        $timeout = 120;
        $model = m::mock('Guzzle\Service\Resource\Model');
        $model->shouldReceive('getPath')->once()->with('Attributes')->andReturn(['VisibilityTimeout'=>$timeout]);

        $this->sqsClient->shouldReceive('getQueueAttributes')->once()->with([
            'QueueUrl' => $url,
            'AttributeNames' => ['VisibilityTimeout']
        ])->andReturn($model);

        return $timeout;
    }

    public function testReceive()
    {
        $url = $this->stubCreateQueue();
        $timeout = $this->stubQueueVisibilityTimeout($url);

        $receiveModel = m::mock('Guzzle\Service\Resource\Model');
        $receiveModel->shouldReceive('getPath')->once()->with('Messages')->andReturn([
            ['Body'=>'foo', 'Attributes'=>[], 'MessageAttributes'=>[], 'MessageId'=>0, 'ReceiptHandle'=>'a']
        ]);
        $this->sqsClient->shouldReceive('receiveMessage')->once()->with([
            'QueueUrl' => $url,
            'AttributeNames' => ['All'],
            'MaxNumberOfMessages' => SqsAdapter::BATCHSIZE_RECEIVE,
            'VisibilityTimeout' => $timeout
        ])->andReturn($receiveModel);

        $deleteModel = m::mock('Guzzle\Service\Resource\Model');
        $deleteModel->shouldReceive('getPath')->once()->with('Failed')->andReturn([]);
        $this->sqsClient->shouldReceive('deleteMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries' => [['Id'=>0, 'ReceiptHandle'=>'a']]
        ])->andReturn($deleteModel);

        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, 100);

        $this->assertCount(1, $msgs);
    }

    public function testReceiveWithPolling()
    {
        $url = $this->stubCreateQueue();
        $timeout = $this->stubQueueVisibilityTimeout($url);

        $receiveModel = m::mock('Guzzle\Service\Resource\Model');
        $receiveModel->shouldReceive('getPath')->once()->with('Messages')->andReturn([
            ['Body'=>'foo', 'Attributes'=>[], 'MessageAttributes'=>[], 'MessageId'=>0, 'ReceiptHandle'=>'a']
        ]);
        $this->sqsClient->shouldReceive('receiveMessage')->once()->with([
            'QueueUrl' => $url,
            'AttributeNames' => ['All'],
            'MaxNumberOfMessages' => SqsAdapter::BATCHSIZE_RECEIVE,
            'VisibilityTimeout' => $timeout
        ])->andReturn($receiveModel);

        $deleteModel = m::mock('Guzzle\Service\Resource\Model');
        $deleteModel->shouldReceive('getPath')->once()->with('Failed')->andReturn([]);
        $this->sqsClient->shouldReceive('deleteMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries' => [['Id'=>0, 'ReceiptHandle'=>'a']]
        ])->andReturn($deleteModel);

        $msgs = [];
        $this->client->receive(function ($msg, $done) use (&$msgs) {
            $msgs[] = $msg;
            $done();
        }, null);

        $this->assertCount(1, $msgs);
    }

    public function testSend()
    {
        $url = $this->stubCreateQueue();
        $model = m::mock('Guzzle\Service\Resource\Model');
        $model->shouldReceive('getPath')->once()->with('Failed')->andReturn([]);

        $this->sqsClient->shouldReceive('sendMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries' => [['Id'=>0, 'MessageBody'=>'foo', 'MessageAttributes'=>[]]]
        ])->andReturn($model);

        $this->client->send([$this->client->create('foo')]);
    }
}
