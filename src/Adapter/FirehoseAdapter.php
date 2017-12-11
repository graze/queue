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

use Aws\Firehose\FirehoseClient;
use Aws\Result;
use Exception;
use Graze\Queue\Adapter\Exception\FailedEnqueueException;
use Graze\Queue\Adapter\Exception\MethodNotSupportedException;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use RuntimeException;

/**
 * Amazon AWS Kinesis Firehose Adapter.
 *
 * This method only supports the enqueue method to send messages to a Kinesiss
 * Firehose stream
 *
 * @link http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Firehose.FirehoseClient.html#putRecordBatch
 */
final class FirehoseAdapter implements AdapterInterface, AsyncAdapterInterface
{
    const BATCHSIZE_SEND = 100;

    /** @var FirehoseClient */
    protected $client;

    /** @var array */
    protected $options;

    /** @var string */
    protected $deliveryStreamName;

    /**
     * @param FirehoseClient $client
     * @param string         $deliveryStreamName
     * @param array          $options - BatchSize <integer> The number of messages to send in each batch.
     */
    public function __construct(FirehoseClient $client, $deliveryStreamName, array $options = [])
    {
        $this->client = $client;
        $this->deliveryStreamName = $deliveryStreamName;
        $this->options = $options;
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @throws MethodNotSupportedException
     */
    public function acknowledge(array $messages)
    {
        throw new MethodNotSupportedException(
            __FUNCTION__,
            $this,
            $messages
        );
    }

    /**
     * @param MessageFactoryInterface $factory
     * @param int                     $limit
     *
     * @return \Iterator|void
     * @throws MethodNotSupportedException
     */
    public function dequeue(MessageFactoryInterface $factory, $limit)
    {
        throw new MethodNotSupportedException(
            __FUNCTION__,
            $this,
            []
        );
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @return PromiseInterface[] List of promises, one for each message
     */
    public function enqueueAsync(array $messages)
    {
        $batches = array_chunk(
            $messages,
            $this->getOption('BatchSize', self::BATCHSIZE_SEND)
        );

        $allPromises = [];

        foreach ($batches as $batch) {
            $requestRecords = array_map(function (MessageInterface $message) {
                return [
                    'Data' => $message->getBody(),
                ];
            },
                $batch);

            $request = [
                'DeliveryStreamName' => $this->deliveryStreamName,
                'Records'            => $requestRecords,
            ];

            /** @var Promise[] $promises */
            $promises = array_map(
                function () {
                    return new Promise();
                },
                $batch
            );

            $this->client->putRecordBatchAsync($request)
                         ->then(function (Result $results) use ($batch, &$promises) {
                             foreach ($results->get('RequestResponses') as $idx => $response) {
                                 if (isset($promises[$idx])) {
                                     if (isset($response['ErrorCode'])) {
                                         $promises[$idx]->reject(
                                             new FailedEnqueueException(
                                                 $this,
                                                 [$batch[$idx]],
                                                 $response
                                             )
                                         );
                                     } else {
                                         $promises[$idx]->resolve($batch[$idx]);
                                     }
                                     unset($promises[$idx]);
                                 } else {
                                     throw new RuntimeException(
                                         'enqueue: unable to find promise for message id: ' . $result['Id']
                                     );
                                 }
                             }

                             // any promises left over should be rejected, because there was no response from the server
                             foreach ($promises as $idx => $promise) {
                                 $promise->reject(new FailedEnqueueException(
                                     $this,
                                     [$batch[$idx]],
                                     ['no response for this message found from the server']
                                 ));
                             }
                         })
                         ->otherwise(function (Exception $e) use (&$promises, $batch) {
                             foreach ($batch as $id => $message) {
                                 if (isset($promises[$id])) {
                                     $promises[$id]->reject(new FailedEnqueueException($this, [$message], [], $e));
                                 }
                             }
                         });

            $allPromises = array_merge($allPromises, $promises);
        }

        return $allPromises;
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
     * @throws MethodNotSupportedException
     */
    public function purge()
    {
        throw new MethodNotSupportedException(
            __FUNCTION__,
            $this,
            []
        );
    }

    /**
     * @throws MethodNotSupportedException
     */
    public function delete()
    {
        throw new MethodNotSupportedException(
            __FUNCTION__,
            $this,
            []
        );
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @return PromiseInterface[] List of promises, once for each message
     */
    public function acknowledgeAsync(array $messages)
    {
        throw new MethodNotSupportedException(
            __FUNCTION__,
            $this,
            $messages
        );
    }

    /**
     * @param MessageFactoryInterface $factory
     * @param int                     $limit
     * @param callable                $onMessage
     *
     * @return void
     */
    public function dequeueAsync(MessageFactoryInterface $factory, $limit, callable $onMessage)
    {
        throw new MethodNotSupportedException(
            __FUNCTION__,
            $this,
            $messages
        );
    }

    /**
     * @return PromiseInterface
     */
    public function purgeAsync()
    {
        throw new MethodNotSupportedException(
            __FUNCTION__,
            $this,
            $messages
        );
    }

    /**
     * @return PromiseInterface
     */
    public function deleteAsync()
    {
        throw new MethodNotSupportedException(
            __FUNCTION__,
            $this,
            $messages
        );
    }
}
