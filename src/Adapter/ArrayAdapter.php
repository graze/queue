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
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use LimitIterator;

final class ArrayAdapter implements AdapterInterface
{
    /** @var MessageInterface[] */
    protected $queue = [];

    /**
     * @param MessageInterface[] $messages
     */
    public function __construct(array $messages = [])
    {
        $this->enqueue($messages);
    }

    /**
     * @param array $messages
     */
    public function acknowledge(array $messages)
    {
        $this->queue = array_values(array_filter($this->queue, function ($message) use ($messages) {
            return false === array_search($message, $messages, true);
        }));
    }

    /**
     * @param MessageFactoryInterface $factory
     * @param int                     $limit
     *
     * @return LimitIterator
     */
    public function dequeue(MessageFactoryInterface $factory, $limit)
    {
        /*
         * If {@see $limit} is null then {@see LimitIterator} should be passed -1 as the count
         * to avoid throwing OutOfBoundsException.
         *
         * @link https://github.com/php/php-src/blob/php-5.6.12/ext/spl/internal/limititerator.inc#L60-L62
         */
        $count = (null === $limit) ? -1 : $limit;

        return new LimitIterator(new ArrayIterator($this->queue), 0, $count);
    }

    /**
     * @param array $messages
     */
    public function enqueue(array $messages)
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        $this->queue = [];
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->purge();
    }

    /**
     * @param MessageInterface $message
     */
    protected function addMessage(MessageInterface $message)
    {
        $this->queue[] = $message;
    }
}
