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
use Aws\Sqs\SqsClient;
use Graze\DataStructure\Container\ContainerInterface;
use Graze\Queue\Adapter\Exception\FailedAcknowledgementException;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;

class SqsAdapterTest extends TestCase
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
    /** @var SqsClient */
    private $client;

    public function setUp()
    {
        $this->client = m::mock(SqsClient::class);
        $this->model = m::mock(ResultInterface::class);
        $this->factory = m::mock(MessageFactoryInterface::class);

        $this->messageA = $a = m::mock(MessageInterface::class);
        $this->messageB = $b = m::mock(MessageInterface::class);
        $this->messageC = $c = m::mock(MessageInterface::class);
        $this->messages = [$a, $b, $c];
    }

    /**
     * @param string $body
     * @param int    $id
     * @param string $handle
     */
    protected function stubCreateDequeueMessage($body, $id, $handle)
    {
        $this->factory->shouldReceive('createMessage')->once()->with(
            $body,
            m::on(function ($opts) use ($id, $handle) {
                $meta = ['Attributes' => [], 'MessageAttributes' => [], 'MessageId' => $id, 'ReceiptHandle' => $handle];
                $validator = isset($opts['validator']) && is_callable($opts['validator']);

                return isset($opts['metadata']) && $opts['metadata'] === $meta && $validator;
            })
        )->andReturn($this->messageA);
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return string
     */
    protected function stubCreateQueue($name, array $options = [])
    {
        $url = 'foo://bar';
        $model = m::mock(ResultInterface::class);
        $model->shouldReceive('get')->once()->with('QueueUrl')->andReturn($url);

        $this->client->shouldReceive('createQueue')->once()->with([
            'QueueName'  => $name,
            'Attributes' => $options,
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

        $this->client->shouldReceive('getQueueAttributes')->once()->with([
            'QueueUrl'       => $url,
            'AttributeNames' => ['VisibilityTimeout'],
        ])->andReturn($model);

        return $timeout;
    }

    public function testInterface()
    {
        assertThat(new SqsAdapter($this->client, 'foo'), is(anInstanceOf('Graze\Queue\Adapter\AdapterInterface')));
    }

    public function testAcknowledge()
    {
        $adapter = new SqsAdapter($this->client, 'foo');
        $url = $this->stubCreateQueue('foo');

        $this->messageA->shouldReceive('getMetadata->get')->once()->with('ReceiptHandle')->andReturn('foo');
        $this->messageB->shouldReceive('getMetadata->get')->once()->with('ReceiptHandle')->andReturn('bar');
        $this->messageC->shouldReceive('getMetadata->get')->once()->with('ReceiptHandle')->andReturn('baz');

        $this->model->shouldReceive('get')->once()->with('Failed')->andReturn([]);

        $this->client->shouldReceive('deleteMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries'  => [
                ['Id' => 0, 'ReceiptHandle' => 'foo'],
                ['Id' => 1, 'ReceiptHandle' => 'bar'],
                ['Id' => 2, 'ReceiptHandle' => 'baz'],
            ],
        ])->andReturn($this->model);

        $adapter->acknowledge($this->messages);
    }

    public function testFailureToAcknowledgeForSomeMessages()
    {
        $adapter = new SqsAdapter($this->client, 'foo');
        $url = $this->stubCreateQueue('foo');

        $this->messageA->shouldReceive('getMetadata->get')->once()->with('ReceiptHandle')->andReturn('foo');
        $this->messageB->shouldReceive('getMetadata->get')->once()->with('ReceiptHandle')->andReturn('bar');
        $this->messageC->shouldReceive('getMetadata->get')->once()->with('ReceiptHandle')->andReturn('baz');

        $this->model->shouldReceive('get')->once()->with('Failed')->andReturn([
            ['Id' => 2, 'Code' => 123, 'SenderFault' => true, 'Message' => 'baz is gone'],
        ]);

        $this->client->shouldReceive('deleteMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries'  => [
                ['Id' => 0, 'ReceiptHandle' => 'foo'],
                ['Id' => 1, 'ReceiptHandle' => 'bar'],
                ['Id' => 2, 'ReceiptHandle' => 'baz'],
            ],
        ])->andReturn($this->model);

        $errorThrown = false;
        try {
            $adapter->acknowledge($this->messages);
        } catch (FailedAcknowledgementException $e) {
            assertThat($e->getMessages(), is(anArray([$this->messageC])));
            assertThat(
                $e->getDebug(),
                is(anArray([['Id' => 2, 'Code' => 123, 'SenderFault' => true, 'Message' => 'baz is gone']]))
            );
            $errorThrown = true;
        }

        assertthat('an exception is thrown', $errorThrown);
    }

    public function testReject()
    {
        $adapter = new SqsAdapter($this->client, 'foo');
        $url = $this->stubCreateQueue('foo');

        $this->messageA->shouldReceive('getMetadata->get')->once()->with('ReceiptHandle')->andReturn('foo');
        $this->messageB->shouldReceive('getMetadata->get')->once()->with('ReceiptHandle')->andReturn('bar');
        $this->messageC->shouldReceive('getMetadata->get')->once()->with('ReceiptHandle')->andReturn('baz');

        $this->model->shouldReceive('get')->once()->with('Failed')->andReturn([]);

        $this->client->shouldReceive('changeMessageVisibilityBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries'  => [
                ['Id' => 0, 'ReceiptHandle' => 'foo', 'VisibilityTimeout' => 0],
                ['Id' => 1, 'ReceiptHandle' => 'bar', 'VisibilityTimeout' => 0],
                ['Id' => 2, 'ReceiptHandle' => 'baz', 'VisibilityTimeout' => 0],
            ],
        ])->andReturn($this->model);

        $adapter->reject($this->messages);
    }

    public function testDequeue()
    {
        $adapter = new SqsAdapter($this->client, 'foo');
        $url = $this->stubCreateQueue('foo');
        $timeout = $this->stubQueueVisibilityTimeout($url);

        $this->stubCreateDequeueMessage('foo', 0, 'a');
        $this->stubCreateDequeueMessage('bar', 1, 'b');
        $this->stubCreateDequeueMessage('baz', 2, 'c');

        $this->model->shouldReceive('get')->once()->with('Messages')->andReturn([
            ['Body' => 'foo', 'Attributes' => [], 'MessageAttributes' => [], 'MessageId' => 0, 'ReceiptHandle' => 'a'],
            ['Body' => 'bar', 'Attributes' => [], 'MessageAttributes' => [], 'MessageId' => 1, 'ReceiptHandle' => 'b'],
            ['Body' => 'baz', 'Attributes' => [], 'MessageAttributes' => [], 'MessageId' => 2, 'ReceiptHandle' => 'c'],
        ]);

        $this->client->shouldReceive('receiveMessage')->once()->with([
            'QueueUrl'            => $url,
            'AttributeNames'      => ['All'],
            'MaxNumberOfMessages' => 3,
            'VisibilityTimeout'   => $timeout,
        ])->andReturn($this->model);

        $iterator = $adapter->dequeue($this->factory, 3);

        assertThat($iterator, is(anInstanceOf('Generator')));
        assertThat(iterator_to_array($iterator), is(equalTo($this->messages)));
    }

    public function testDequeueInBatches()
    {
        $adapter = new SqsAdapter($this->client, 'foo');
        $url = $this->stubCreateQueue('foo');
        $timeout = $this->stubQueueVisibilityTimeout($url);

        $limit = SqsAdapter::BATCHSIZE_RECEIVE;

        $return = [];
        $messages = [];

        for ($i = 0; $i < $limit; $i++) {
            $this->stubCreateDequeueMessage('tmp' . $i, $i, 'h' . $i);
            $return[] = [
                'Body'              => 'tmp' . $i,
                'Attributes'        => [],
                'MessageAttributes' => [],
                'MessageId'         => $i,
                'ReceiptHandle'     => 'h' . $i,
            ];
            $messages[] = $this->messageA;
        }

        $this->model->shouldReceive('get')->once()->with('Messages')->andReturn($return);

        $this->client->shouldReceive('receiveMessage')->once()->with([
            'QueueUrl'            => $url,
            'AttributeNames'      => ['All'],
            'MaxNumberOfMessages' => $limit,
            'VisibilityTimeout'   => $timeout,
        ])->andReturn($this->model);

        $iterator = $adapter->dequeue($this->factory, $limit);

        assertThat($iterator, is(anInstanceOf('Generator')));
        assertThat(iterator_to_array($iterator), is(equalTo($messages)));
    }

    public function testEnqueue()
    {
        $adapter = new SqsAdapter($this->client, 'foo');
        $url = $this->stubCreateQueue('foo');

        $metadata = m::mock(ContainerInterface::class);
        $metadata->shouldReceive('get')
                 ->with('MessageAttributes')
                 ->times(3)
                 ->andReturn(null);
        $metadata->shouldReceive('get')
                 ->with('DelaySeconds')
                 ->andReturn(null);

        $this->messageA->shouldReceive('getBody')->once()->withNoArgs()->andReturn('foo');
        $this->messageB->shouldReceive('getBody')->once()->withNoArgs()->andReturn('bar');
        $this->messageC->shouldReceive('getBody')->once()->withNoArgs()->andReturn('baz');
        $this->messageA->shouldReceive('getMetadata')->andReturn($metadata);
        $this->messageB->shouldReceive('getMetadata')->andReturn($metadata);
        $this->messageC->shouldReceive('getMetadata')->andReturn($metadata);

        $this->model->shouldReceive('get')->once()->with('Failed')->andReturn([]);

        $this->client->shouldReceive('sendMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries'  => [
                ['Id' => 0, 'MessageBody' => 'foo', 'MessageAttributes' => []],
                ['Id' => 1, 'MessageBody' => 'bar', 'MessageAttributes' => []],
                ['Id' => 2, 'MessageBody' => 'baz', 'MessageAttributes' => []],
            ],
        ])->andReturn($this->model);

        $adapter->enqueue($this->messages);
    }

    public function testEnqueueWithDelaySecondsMetadata()
    {
        $adapter = new SqsAdapter($this->client, 'foo');
        $url = $this->stubCreateQueue('foo');

        $metadataA = m::mock(ContainerInterface::class);
        $metadataA->shouldReceive('get')->with('MessageAttributes')->once()->andReturn(null);
        $metadataA->shouldReceive('get')->with('DelaySeconds')->andReturn(1);
        $metadataB = m::mock(ContainerInterface::class);
        $metadataB->shouldReceive('get')->with('MessageAttributes')->once()->andReturn(null);
        $metadataB->shouldReceive('get')->with('DelaySeconds')->andReturn(2);
        $metadataC = m::mock(ContainerInterface::class);
        $metadataC->shouldReceive('get')->with('MessageAttributes')->once()->andReturn(null);
        $metadataC->shouldReceive('get')->with('DelaySeconds')->andReturn(3);

        $this->messageA->shouldReceive('getBody')->once()->withNoArgs()->andReturn('foo');
        $this->messageB->shouldReceive('getBody')->once()->withNoArgs()->andReturn('bar');
        $this->messageC->shouldReceive('getBody')->once()->withNoArgs()->andReturn('baz');
        $this->messageA->shouldReceive('getMetadata')->andReturn($metadataA);
        $this->messageB->shouldReceive('getMetadata')->andReturn($metadataB);
        $this->messageC->shouldReceive('getMetadata')->andReturn($metadataC);

        $this->model->shouldReceive('get')->once()->with('Failed')->andReturn([]);

        $this->client->shouldReceive('sendMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries'  => [
                ['Id' => 0, 'MessageBody' => 'foo', 'MessageAttributes' => [], 'DelaySeconds' => 1],
                ['Id' => 1, 'MessageBody' => 'bar', 'MessageAttributes' => [], 'DelaySeconds' => 2],
                ['Id' => 2, 'MessageBody' => 'baz', 'MessageAttributes' => [], 'DelaySeconds' => 3],
            ],
        ])->andReturn($this->model);

        $adapter->enqueue($this->messages);
    }

    public function testEnqueueWithDelaySecondsQueueConfiguration()
    {
        $options = ['DelaySeconds' => 10];

        $adapter = new SqsAdapter($this->client, 'foo', $options);
        $url = $this->stubCreateQueue('foo', $options);

        $metadataA = m::mock(ContainerInterface::class);
        $metadataA->shouldReceive('get')->with('MessageAttributes')->once()->andReturn(null);
        $metadataA->shouldReceive('get')->with('DelaySeconds')->andReturn(null);
        $metadataB = m::mock(ContainerInterface::class);
        $metadataB->shouldReceive('get')->with('MessageAttributes')->once()->andReturn(null);
        $metadataB->shouldReceive('get')->with('DelaySeconds')->andReturn(0);
        $metadataC = m::mock(ContainerInterface::class);
        $metadataC->shouldReceive('get')->with('MessageAttributes')->once()->andReturn(null);
        $metadataC->shouldReceive('get')->with('DelaySeconds')->andReturn(2);

        $this->messageA->shouldReceive('getBody')->once()->withNoArgs()->andReturn('foo');
        $this->messageB->shouldReceive('getBody')->once()->withNoArgs()->andReturn('bar');
        $this->messageC->shouldReceive('getBody')->once()->withNoArgs()->andReturn('baz');
        $this->messageA->shouldReceive('getMetadata')->andReturn($metadataA);
        $this->messageB->shouldReceive('getMetadata')->andReturn($metadataB);
        $this->messageC->shouldReceive('getMetadata')->andReturn($metadataC);

        $this->model->shouldReceive('get')->once()->with('Failed')->andReturn([]);

        $this->client->shouldReceive('sendMessageBatch')->once()->with([
            'QueueUrl' => $url,
            'Entries'  => [
                ['Id' => 0, 'MessageBody' => 'foo', 'MessageAttributes' => []],
                ['Id' => 1, 'MessageBody' => 'bar', 'MessageAttributes' => [], 'DelaySeconds' => 0],
                ['Id' => 2, 'MessageBody' => 'baz', 'MessageAttributes' => [], 'DelaySeconds' => 2],
            ],
        ])->andReturn($this->model);

        $adapter->enqueue($this->messages);
    }

    public function testReceiveMessageWaitTimeSecondsOption()
    {
        $options = ['ReceiveMessageWaitTimeSeconds' => 20];

        $adapter = new SqsAdapter($this->client, 'foo', $options);
        $url = $this->stubCreateQueue('foo', $options);
        $timeout = $this->stubQueueVisibilityTimeout($url);

        $this->stubCreateDequeueMessage('foo', 0, 'a');
        $this->stubCreateDequeueMessage('bar', 1, 'b');
        $this->stubCreateDequeueMessage('baz', 2, 'c');

        $this->model->shouldReceive('get')->once()->with('Messages')->andReturn([
            ['Body' => 'foo', 'Attributes' => [], 'MessageAttributes' => [], 'MessageId' => 0, 'ReceiptHandle' => 'a'],
            ['Body' => 'bar', 'Attributes' => [], 'MessageAttributes' => [], 'MessageId' => 1, 'ReceiptHandle' => 'b'],
            ['Body' => 'baz', 'Attributes' => [], 'MessageAttributes' => [], 'MessageId' => 2, 'ReceiptHandle' => 'c'],
        ]);

        $this->client->shouldReceive('receiveMessage')->once()->with([
            'QueueUrl'            => $url,
            'AttributeNames'      => ['All'],
            'MaxNumberOfMessages' => 3,
            'VisibilityTimeout'   => $timeout,
            'WaitTimeSeconds'     => 20,
        ])->andReturn($this->model);

        $iterator = $adapter->dequeue($this->factory, 3);

        assertThat($iterator, is(anInstanceOf('Generator')));
        assertThat(iterator_to_array($iterator), is(equalTo($this->messages)));
    }

    public function testPurge()
    {
        $adapter = new SqsAdapter($this->client, 'foo');
        $url = $this->stubCreateQueue('foo');

        $this->client->shouldReceive('purgeQueue')->once()->with([
            'QueueUrl' => $url,
        ])->andReturn($this->model);

        assertThat($adapter->purge(), is(nullValue()));
    }

    public function testDelete()
    {
        $adapter = new SqsAdapter($this->client, 'foo');
        $url = $this->stubCreateQueue('foo');

        $this->client->shouldReceive('deleteQueue')->once()->with([
            'QueueUrl' => $url,
        ])->andReturn($this->model);

        assertThat($adapter->delete(), is(nullValue()));
    }
}
