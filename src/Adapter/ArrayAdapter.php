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
 * @link https://github.com/graze/queue
 */

namespace Graze\Queue\Adapter;

use ArrayIterator;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use LimitIterator;

class ArrayAdapter implements AdapterInterface
{
    /**
     * @param MessageInterface[]
     */
    protected $queue;

    /**
     * @param MessageInterface[] $messages
     */
    public function __construct(array $messages = [])
    {
        $this->enqueue($messages);
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(array $messages)
    {
        $this->queue = array_values(array_filter($this->queue, function ($message) use ($messages) {
            return false === array_search($message, $messages, true);
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue(MessageFactoryInterface $factory, $limit)
    {
        $total = null === $limit ? count($this->queue) : $limit;

        return new LimitIterator(new ArrayIterator($this->queue), 0, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(array $messages)
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }
    }

    /**
     * @param MessageInterface $message
     */
    protected function addMessage(MessageInterface $message)
    {
        $this->queue[] = $message;
    }
}
