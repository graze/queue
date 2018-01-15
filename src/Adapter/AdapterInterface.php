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

use Graze\Queue\Adapter\Exception\FailedAcknowledgementException;
use Graze\Queue\Adapter\Exception\FailedEnqueueException;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use Iterator;

interface AdapterInterface
{
    /**
     * Acknowledge the messages (delete them from the queue)
     *
     * @param MessageInterface[] $messages
     *
     * @return void
     *
     * @throws FailedAcknowledgementException
     */
    public function acknowledge(array $messages);

    /**
     * Attempt to reject all the following messages (make the message immediately visible to other consumers)
     *
     * @param MessageInterface[] $messages
     *
     * @return void
     *
     * @throws FailedAcknowledgementException
     */
    public function reject(array $messages);

    /**
     * Remove up to {$limit} messages from the queue
     *
     * @param MessageFactoryInterface $factory
     * @param int                     $limit
     *
     * @return Iterator
     */
    public function dequeue(MessageFactoryInterface $factory, $limit);

    /**
     * Add all the messages to the queue
     *
     * @param MessageInterface[] $messages
     *
     * @return void
     *
     * @throws FailedEnqueueException
     */
    public function enqueue(array $messages);

    /**
     * Empty the queue
     *
     * @return void
     */
    public function purge();

    /**
     * Delete the queue
     *
     * @return void
     */
    public function delete();
}
