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
use Aws\Sqs\SqsClient;
use Graze\Queue\Adapter\SqsAdapter;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;

class SqsIntegrationTest extends TestCase
{
    /** @var string */
    private $queueName;
    /** @var SqsClient|MockInterface */
    private $sqsClient;
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->queueName = 'queue_foo';
        $this->sqsClient = m::mock(SqsClient::class);
        $this->client = new Client(new SqsAdapter($this->sqsClient, 'queue_foo'));
    }

    /**
     * Create a queue
     *
     * @return string
     */
    protected function stubCreateQueue()
    {
        $url = 'queue://foo';
        $model = m::mock(ResultInterface::class);
        $model->shouldReceive('get')->once()->with('QueueUrl')->andReturn($url);

        $this->sqsClient->shouldReceive('createQueue')->once()->with([
            'QueueName'  => $this->queueName,
            'Attributes' => [],
        ])->andReturn($model);

        return $url;
    }

    /**
     * @param string $url
     *
     * @return int
     */
    protected function stubQueueVisibilityTimeout($url)
    {
        $timeout = 120;
        $model = m::mock(ResultInterface::class);
        $model->shouldReceive('get')->once()->with('Attributes')->andReturn(['VisibilityTimeout' => $timeout]);

        $this->sqsClient->shouldReceive('getQueueAttributes')->once()->with([
            'QueueUrl'       => $url,
            'AttributeNames' => ['VisibilityTimeout'],
        ])->andReturn($model);

        return $timeout;
    }

    public function testReceive()
    {
        $url = $this->stubCreateQueue();
        $timeout = $this->stubQueueVisibilityTimeout($url);

        $receiveModel = m::mock(ResultInterface::class);
        $receiveModel->shouldReceive('get')->once()->with('Messages')->andReturn([
            ['Body' => 'foo', 'Attributes' => [], 'MessageAttributes' => [], 'MessageId' => 0, 'ReceiptHandle' => 'a'],
        ]);
        $this->sqsClient->shouldReceive('receiveMessage')->once()->with([
            'QueueUrl'            => $url,
            'AttributeNames'      => ['All'],
            'MaxNumberOfMessages' => 1,
            'VisibilityTimeout'   => $timeout,
        ])->andReturn($receiveModel);

        $deleteModel = m::mock(ResultInterface::class);
        $deleteModel->shouldReceive('get')->once()->with('Failed')->andReturn([]);
        $this->sqsClient->shouldReceive('deleteMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries'  => [['Id' => 0, 'ReceiptHandle' => 'a']],
        ])->andReturn($deleteModel);

        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        });

        assertThat($msgs, is(arrayWithSize(1)));
    }

    public function testReceiveWithReceiveMessageReturningLessThanMaxNumberOfMessages()
    {
        $url = $this->stubCreateQueue();
        $timeout = $this->stubQueueVisibilityTimeout($url);

        $receiveModel = m::mock(ResultInterface::class);
        $receiveModel->shouldReceive('get')->with('Messages')->andReturn(
            [
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
            ],
            [
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
                ['Body'              => 'foo',
                 'Attributes'        => [],
                 'MessageAttributes' => [],
                 'MessageId'         => 0,
                 'ReceiptHandle'     => 'a',
                ],
            ],
            null
        );

        $this->sqsClient->shouldReceive('receiveMessage')->andReturn($receiveModel);

        $deleteModel = m::mock(ResultInterface::class);
        $deleteModel->shouldReceive('get')->twice()->with('Failed')->andReturn([]);
        $this->sqsClient->shouldReceive('deleteMessageBatch')->with(m::type('array'))->andReturn($deleteModel);

        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, 11);

        assertThat($msgs, is(arrayWithSize(11)));
    }

    public function testReceiveWithLimit()
    {
        $url = $this->stubCreateQueue();
        $timeout = $this->stubQueueVisibilityTimeout($url);

        $receiveModel = m::mock(ResultInterface::class);
        $receiveModel->shouldReceive('get')->once()->with('Messages')->andReturn([
            ['Body' => 'foo', 'Attributes' => [], 'MessageAttributes' => [], 'MessageId' => 0, 'ReceiptHandle' => 'a'],
        ]);
        $this->sqsClient->shouldReceive('receiveMessage')->once()->with([
            'QueueUrl'            => $url,
            'AttributeNames'      => ['All'],
            'MaxNumberOfMessages' => SqsAdapter::BATCHSIZE_RECEIVE,
            'VisibilityTimeout'   => $timeout,
        ])->andReturn($receiveModel);

        $deleteModel = m::mock(ResultInterface::class);
        $deleteModel->shouldReceive('get')->once()->with('Failed')->andReturn([]);
        $this->sqsClient->shouldReceive('deleteMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries'  => [['Id' => 0, 'ReceiptHandle' => 'a']],
        ])->andReturn($deleteModel);

        $msgs = [];
        $this->client->receive(function ($msg, $done) use (&$msgs) {
            $msgs[] = $msg;
            $done();
        }, 100);

        assertThat($msgs, is(arrayWithSize(1)));
    }

    public function testReceiveWithPolling()
    {
        $url = $this->stubCreateQueue();
        $timeout = $this->stubQueueVisibilityTimeout($url);

        $receiveModel = m::mock(ResultInterface::class);
        $receiveModel->shouldReceive('get')->once()->with('Messages')->andReturn([
            ['Body' => 'foo', 'Attributes' => [], 'MessageAttributes' => [], 'MessageId' => 0, 'ReceiptHandle' => 'a'],
        ]);
        $this->sqsClient->shouldReceive('receiveMessage')->once()->with([
            'QueueUrl'            => $url,
            'AttributeNames'      => ['All'],
            'MaxNumberOfMessages' => SqsAdapter::BATCHSIZE_RECEIVE,
            'VisibilityTimeout'   => $timeout,
        ])->andReturn($receiveModel);

        $deleteModel = m::mock(ResultInterface::class);
        $deleteModel->shouldReceive('get')->once()->with('Failed')->andReturn([]);
        $this->sqsClient->shouldReceive('deleteMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries'  => [['Id' => 0, 'ReceiptHandle' => 'a']],
        ])->andReturn($deleteModel);

        $msgs = [];
        $this->client->receive(function ($msg, $done) use (&$msgs) {
            $msgs[] = $msg;
            $done();
        }, null);

        assertThat($msgs, is(arrayWithSize(1)));
    }

    public function testSend()
    {
        $url = $this->stubCreateQueue();
        $model = m::mock(ResultInterface::class);
        $model->shouldReceive('get')->once()->with('Failed')->andReturn([]);

        $this->sqsClient->shouldReceive('sendMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries'  => [['Id' => 0, 'MessageBody' => 'foo', 'MessageAttributes' => []]],
        ])->andReturn($model);

        $this->client->send([$this->client->create('foo')]);
    }

    public function testPurge()
    {
        $url = $this->stubCreateQueue();
        $timeout = $this->stubQueueVisibilityTimeout($url);

        $receiveModel = m::mock(ResultInterface::class);
        $receiveModel->shouldReceive('get')->once()->with('Messages')->andReturn([]);
        $this->sqsClient->shouldReceive('receiveMessage')->once()->with([
            'QueueUrl'            => $url,
            'AttributeNames'      => ['All'],
            'MaxNumberOfMessages' => 1,
            'VisibilityTimeout'   => $timeout,
        ])->andReturn($receiveModel);

        $purgeModel = m::mock(ResultInterface::class);
        $this->sqsClient->shouldReceive('purgeQueue')->once()->with([
            'QueueUrl' => $url,
        ])->andReturn($purgeModel);

        $this->client->purge();

        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        });

        assertThat($msgs, is(emptyArray()));
    }

    public function testDelete()
    {
        $url = $this->stubCreateQueue();

        $deleteModel = m::mock(ResultInterface::class);
        $this->sqsClient->shouldReceive('deleteQueue')->once()->with([
            'QueueUrl' => $url,
        ])->andReturn($deleteModel);

        $this->client->delete();
    }
}
