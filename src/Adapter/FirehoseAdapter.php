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
use Graze\Queue\Adapter\Exception\FailedEnqueueException;
use Graze\Queue\Adapter\Exception\MethodNotSupportedException;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;

/**
 * Amazon AWS Kinesis Firehose Adapter.
 *
 * This method only supports the enqueue method to send messages to a Kinesiss
 * Firehose stream
 *
 * @link http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.Firehose.FirehoseClient.html#putRecordBatch
 */
final class FirehoseAdapter implements AdapterInterface
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
     * @param MessageInterface[] $messages
     * @param int                $duration Number of seconds to ensure that this message stays being processed and not
     *                                     put back on the queue
     */
    public function extend(array $messages, $duration)
    {
        throw new MethodNotSupportedException(
            __FUNCTION__,
            $this,
            $messages
        );
    }

    /**
     * @param MessageInterface[] $messages
     */
    public function reject(array $messages)
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
     * @throws FailedEnqueueException
     */
    public function enqueue(array $messages)
    {
        $failed = [];
        $batches = array_chunk(
            $messages,
            $this->getOption('BatchSize', self::BATCHSIZE_SEND)
        );

        foreach ($batches as $batch) {
            $requestRecords = array_map(
                function (MessageInterface $message) {
                    return [
                        'Data' => $message->getBody(),
                    ];
                },
                $batch
            );

            $request = [
                'DeliveryStreamName' => $this->deliveryStreamName,
                'Records'            => $requestRecords,
            ];

            $results = $this->client->putRecordBatch($request);

            foreach ($results->get('RequestResponses') as $idx => $response) {
                if (isset($response['ErrorCode'])) {
                    $failed[] = $batch[$idx];
                }
            }
        }

        if (!empty($failed)) {
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
}
