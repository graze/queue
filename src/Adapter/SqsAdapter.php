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

use ArrayIterator;
use Aws\Result;
use Aws\Sqs\SqsClient;
use Exception;
use Graze\Queue\Adapter\Exception\FailedAcknowledgementException;
use Graze\Queue\Adapter\Exception\FailedEnqueueException;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use RuntimeException;

/**
 * Amazon AWS SQS Adapter.
 *
 * By default this adapter uses standard polling, which may return an empty response
 * even if messages exist on the queue.
 *
 * > This happens when Amazon SQS uses short (standard) polling, the default behavior,
 * > where only a subset of the servers (based on a weighted random distribution) are
 * > queried to see if any messages are available to include in the response.
 *
 * You may want to consider setting the `ReceiveMessageWaitTimeSeconds`
 * option to enable long polling the queue, which queries all of the servers.
 *
 * @link https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-long-polling.html
 * @link http://docs.aws.amazon.com/aws-sdk-php/guide/latest/service-sqs.html
 * @link http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sqs.SqsClient.html#_createQueue
 * @link http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sqs.SqsClient.html#_deleteMessageBatch
 * @link http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sqs.SqsClient.html#_receiveMessage
 * @link http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Sqs.SqsClient.html#_sendMessageBatch
 */
final class SqsAdapter implements AdapterInterface, AsyncAdapterInterface, NamedInterface
{
    const BATCHSIZE_DELETE  = 10;
    const BATCHSIZE_RECEIVE = 10;
    const BATCHSIZE_SEND    = 10;

    /** @var SqsClient */
    protected $client;

    /** @var array */
    protected $options;

    /** @var string */
    protected $name;

    /** @var string */
    protected $url;

    /**
     * @param SqsClient $client
     * @param string    $name
     * @param array     $options - DelaySeconds <integer> The time in seconds that the delivery of all
     *                           messages in the queue will be delayed.
     *                           - MaximumMessageSize <integer> The limit of how many bytes a message
     *                           can contain before Amazon SQS rejects it.
     *                           - MessageRetentionPeriod <integer> The number of seconds Amazon SQS
     *                           retains a message.
     *                           - Policy <string> The queue's policy. A valid form-url-encoded policy.
     *                           - ReceiveMessageWaitTimeSeconds <integer> The time for which a
     *                           ReceiveMessage call will wait for a message to arrive.
     *                           - VisibilityTimeout <integer> The visibility timeout for the queue.
     */
    public function __construct(SqsClient $client, $name, array $options = [])
    {
        $this->client = $client;
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @return PromiseInterface[]
     */
    public function acknowledgeAsync(array $messages)
    {
        $url = $this->getQueueUrl();
        $batches = array_chunk($this->createDeleteEntries($messages), self::BATCHSIZE_DELETE);

        /** @var Promise[] $promises */
        $promises = array_map(
            function () {
                return new Promise();
            },
            $messages
        );

        foreach ($batches as $batch) {
            $this->client->deleteMessageBatchAsync([
                'QueueUrl' => $url,
                'Entries'  => $batch,
            ])->then(function (Result $results) use ($messages, &$promises) {
                foreach (($results->get('Failed') ?: []) as $result) {
                    if (isset($promises[$result['Id']])) {
                        $promises[$result['Id']]->reject(
                            new FailedAcknowledgementException(
                                $this,
                                [$messages[$result['Id']]],
                                $result
                            )
                        );
                    } else {
                        throw new RuntimeException(
                            'acknowledge: unable to find promise for message id: ' . $result['Id']
                        );
                    }
                }

                foreach (($results->get('Successful')) as $result) {
                    if (isset($promises[$result['Id']])) {
                        $promises[$result['Id']]->resolve($messages[$result['Id']]);
                    } else {
                        throw new RuntimeException(
                            'acknowledge: unable to find promise for message id: ' . $result['Id']
                        );
                    }
                }
            })->otherwise(function (Exception $e) use ($promises, $batch) {
                foreach ($batch as $id => $message) {
                    if (isset($promises[$id])) {
                        $promises[$id]->reject(new FailedAcknowledgementException($this, [$message], [], $e));
                    }
                }
            });
        }

        return $promises;
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @throws FailedAcknowledgementException if any messages failed to enqueue
     */
    public function acknowledge(array $messages)
    {
        $promises = $this->acknowledgeAsync($messages);
        $failed = [];

        \GuzzleHttp\Promise\each(
            $promises,
            null,
            function (FailedAcknowledgementException $e) use (&$failed) {
                $failed = array_merge($failed, $e->getMessages());
            }
        )->wait();

        if (count($failed) > 0) {
            throw new FailedAcknowledgementException($this, $failed);
        }
    }

    /**
     * @param MessageFactoryInterface $factory
     * @param int                     $limit
     * @param callable                $onMessage
     *
     * @return PromiseInterface
     */
    public function dequeueAsync(MessageFactoryInterface $factory, $limit, callable $onMessage)
    {
        $remaining = $limit ?: 0;

        $promises = [];

        while (null === $limit || $remaining > 0) {
            /**
             * If a limit has been specified, set {@see $size} so that we don't return more
             * than the requested number of messages if it's less than the batch size.
             */
            $size = ($limit !== null) ? min($remaining, self::BATCHSIZE_RECEIVE) : self::BATCHSIZE_RECEIVE;

            $timestamp = time() + $this->getQueueVisibilityTimeout();
            $validator = function () use ($timestamp) {
                return time() < $timestamp;
            };

            $promises[] = $this->client->receiveMessageAsync(array_filter([
                'QueueUrl'            => $this->getQueueUrl(),
                'AttributeNames'      => ['All'],
                'MaxNumberOfMessages' => $size,
                'VisibilityTimeout'   => $this->getOption('VisibilityTimeout'),
                'WaitTimeSeconds'     => $this->getOption('ReceiveMessageWaitTimeSeconds'),
            ]))->then(function (Result $results) use ($onMessage, $factory, $validator) {
                $messages = $results->get('Messages') ?: [];

                if (count($messages) === 0) {
                    return;
                }

                foreach ($messages as $result) {
                    $onMessage(
                        $factory->createMessage(
                            $result['Body'],
                            [
                                'metadata'  => $this->createMessageMetadata($result),
                                'validator' => $validator,
                            ]
                        )
                    );
                }
            });

            // Decrement the number of messages remaining.
            $remaining -= $size;
        }

        return \GuzzleHttp\Promise\settle($promises);
    }

    /**
     * @param MessageFactoryInterface $factory
     * @param int                     $limit
     *
     * @return Iterator
     */
    public function dequeue(MessageFactoryInterface $factory, $limit)
    {
        $messages = [];

        $this->dequeueAsync($factory,
            $limit,
            function (MessageInterface $message) use (&$messages) {
                $messages[] = $message;
            }
        )->wait();

        return new ArrayIterator($messages);
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @return PromiseInterface[] List of promises, one per message
     */
    public function enqueueAsync(array $messages)
    {
        $url = $this->getQueueUrl();
        $batches = array_chunk($this->createEnqueueEntries($messages), self::BATCHSIZE_SEND);

        /** @var Promise[] $promises */
        $promises = array_map(
            function () {
                return new Promise();
            },
            $messages
        );

        foreach ($batches as $batch) {
            $this->client->sendMessageBatchAsync([
                'QueueUrl' => $url,
                'Entries'  => $batch,
            ])->then(function (Result $results) use ($messages, &$promises) {
                foreach (($results->get('Failed') ?: []) as $result) {
                    if (isset($promises[$result['Id']])) {
                        $promises[$result['Id']]->reject(
                            new FailedEnqueueException(
                                $this,
                                [$messages[$result['Id']]],
                                $result
                            )
                        );
                    } else {
                        throw new RuntimeException(
                            'acknowledge: unable to find promise for message id: ' . $result['Id']
                        );
                    }
                }

                foreach (($results->get('Successful')) as $result) {
                    if (isset($promises[$result['Id']])) {
                        $promises[$result['Id']]->resolve($messages[$result['Id']]);
                    } else {
                        throw new RuntimeException(
                            'acknowledge: unable to find promise for message id: ' . $result['Id']
                        );
                    }
                }
            })->otherwise(function (Exception $e) use ($promises, $batch) {
                foreach ($batch as $id => $message) {
                    if (isset($promises[$id])) {
                        $promises[$id]->reject(new FailedEnqueueException($this, [$message], [], $e));
                    }
                }
            });
        }

        return $promises;
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @throws FailedEnqueueException
     */
    public function enqueue(array $messages)
    {
        $promises = $this->enqueueAsync($messages);
        $failed = [];

        \GuzzleHttp\Promise\each(
            $promises,
            null,
            function (FailedEnqueueException $e) use (&$failed) {
                $failed = array_merge($failed, $e->getMessages());
            }
        )->wait();

        if (count($failed) > 0) {
            throw new FailedEnqueueException($this, $failed);
        }
    }

    /**
     * @return PromiseInterface
     */
    public function purgeAsync()
    {
        return $this->client->purgeQueueAsync(['QueueUrl' => $this->getQueueUrl()]);
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        $this->purgeAsync()->wait();
    }

    /**
     * @return PromiseInterface
     */
    public function deleteAsync()
    {
        return $this->client->deleteQueueAsync(['QueueUrl' => $this->getQueueUrl()]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->deleteAsync()->wait();
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @return array
     */
    protected function createDeleteEntries(array $messages)
    {
        array_walk($messages,
            function (MessageInterface &$message, $id) {
                $metadata = $message->getMetadata();
                $message = [
                    'Id'            => $id,
                    'ReceiptHandle' => $metadata->get('ReceiptHandle'),
                ];
            });

        return $messages;
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @return array
     */
    protected function createEnqueueEntries(array $messages)
    {
        array_walk($messages,
            function (MessageInterface &$message, $id) {
                $metadata = $message->getMetadata();
                $message = [
                    'Id'                => $id,
                    'MessageBody'       => $message->getBody(),
                    'MessageAttributes' => $metadata->get('MessageAttributes') ?: [],
                ];
                if (!is_null($metadata->get('DelaySeconds'))) {
                    $message['DelaySeconds'] = $metadata->get('DelaySeconds');
                }
            });

        return $messages;
    }

    /**
     * @param array $result
     *
     * @return array
     */
    protected function createMessageMetadata(array $result)
    {
        return array_intersect_key($result,
            [
                'Attributes'        => [],
                'MessageAttributes' => [],
                'MessageId'         => null,
                'ReceiptHandle'     => null,
            ]);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * @return string
     */
    protected function getQueueUrl()
    {
        if (!$this->url) {
            $result = $this->client->createQueue([
                'QueueName'  => $this->name,
                'Attributes' => $this->options,
            ]);

            $this->url = $result->get('QueueUrl');
        }

        return $this->url;
    }

    /**
     * @return int
     */
    protected function getQueueVisibilityTimeout()
    {
        if (!isset($this->options['VisibilityTimeout'])) {
            $result = $this->client->getQueueAttributes([
                'QueueUrl'       => $this->getQueueUrl(),
                'AttributeNames' => ['VisibilityTimeout'],
            ]);

            $attributes = $result->get('Attributes');
            $this->options['VisibilityTimeout'] = $attributes['VisibilityTimeout'];
        }

        return $this->options['VisibilityTimeout'];
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->name;
    }
}
