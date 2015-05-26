<?php

/**
 * This file is part of graze/queue.
 *
 * Copyright (c) 2015 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license http://github.com/graze/queue/blob/master/LICENSE MIT
 * @link http://github.com/graze/queue
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
