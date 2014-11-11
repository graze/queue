<?php
/*
 * This file is part of Graze Queue
 *
 * Copyright (c) 2014 Nature Delivered Ltd. <http://graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see  http://github.com/graze/queue/blob/master/LICENSE
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
