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
use Graze\Queue\Adapter\Exception\MethodNotSupportedException;
use Graze\Queue\Adapter\Exception\FailedEnqueueException;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;

/**
 * Amazon AWS Kinesis Firehose Adapter.
 *
 * This method only supports the enqueue method to send messages to a Kinesiss
 * Firehose stream
 *
 */
final class FirehoseAdapter implements AdapterInterface
{
    const BATCHSIZE_SEND    = 100;

    /** @var FirehoseClient */
    protected $client;

    /** @var array */
    protected $options;

    /** @var string */
    protected $deliveryStreamName;

    /**
     * @param FirehoseClient $client
     * @param string    $deliveryStreamName
     * @param array     $options
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
            'acknowledge',
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
            'dequeue',
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
        $batches = array_chunk($this->createEnqueueEntries($messages), self::BATCHSIZE_SEND);

        foreach ($batches as $batch) {
            $requestRecords = array_map(function ($a) {
                return [
                    'Data' => json_encode($a)
                ];
            }, $batch);

            $request = [
                'DeliveryStreamName' => $this->deliveryStreamName,
                'Records'  => $requestRecords,
            ];

            $results = $this->client->putRecordBatch($request);

            foreach ($results->get('RequestResponses') as $idx => $response) {
                if (isset($response['ErrorCode'])) {
                    $failed[] = $messages[$batch[$idx]['Id']];
                }
            }
        }

        if (!empty($failed)) {
            throw new FailedEnqueueException($this, $failed);
        }
    }

    /**
     * @throws MethodNotSupportedException
     */
    public function purge()
    {
        throw new MethodNotSupportedException(
            'purge',
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
            'delete',
            $this,
            []
        );
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @return array
     */
    protected function createEnqueueEntries(array $messages)
    {
        array_walk($messages, function (MessageInterface &$message, $id) {
            $metadata = $message->getMetadata();
            $message = [
                'Id'                => $id,
                'MessageBody'       => $message->getBody(),
                'MessageAttributes' => $metadata->get('MessageAttributes') ?: [],
            ];
        });

        return $messages;
    }
}
