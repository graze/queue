<?php
namespace Graze\Queue\Adapter;

use Graze\Queue\Adapter\Exception\FailedAcknowledgementException;
use Graze\Queue\Adapter\Exception\FailedEnqueueException;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use Iterator;

interface AdapterInterface
{
    /**
     * @param MessageInterface[] $messages
     * @throws FailedAcknowledgementException
     */
    public function acknowledge(array $messages);

    /**
     * @param MessageFactoryInterface $factory
     * @param integer $limit
     * @return Iterator
     */
    public function dequeue(MessageFactoryInterface $factory, $limit);

    /**
     * @param MessageInterface[] $messages
     * @throws FailedEnqueueException
     */
    public function enqueue(array $messages);
}
