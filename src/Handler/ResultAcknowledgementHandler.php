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

namespace Graze\Queue\Handler;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

class ResultAcknowledgementHandler extends AbstractAcknowledgementHandler
{
    /** @var callable */
    private $validator;

    /** @var AbstractAcknowledgementHandler */
    private $handler;

    /**
     * ResultAcknowledgementHandler constructor.
     *
     * @param callable                       $validator
     * @param AbstractAcknowledgementHandler $handler
     */
    public function __construct(callable $validator, AbstractAcknowledgementHandler $handler)
    {
        /**
         * This callable should accept the mixed result returned by a worker
         * and return a boolean value.
         *
         * @var callable
         */
        $this->validator = $validator;

        /**
         * The handler to call `acknowledge` or `reject` on if {@see $validator} returns a
         * truthy value for the given result.
         *
         * @var AbstractAcknowledgementHandler
         */
        $this->handler = $handler;
    }

    /**
     * @param MessageInterface $message
     * @param AdapterInterface $adapter
     * @param mixed            $result
     */
    protected function acknowledge(
        MessageInterface $message,
        AdapterInterface $adapter,
        $result = null
    ) {
        if (call_user_func($this->validator, $result) === true) {
            $this->handler->acknowledge($message, $adapter, $result);
        } else {
            $this->handler->reject($message, $adapter, $result);
        }
    }

    /**
     * @param MessageInterface $message
     * @param AdapterInterface $adapter
     * @param int              $duration Number of seconds to ensure that this message is not seen by any other clients
     */
    protected function extend(
        MessageInterface $message,
        AdapterInterface $adapter,
        $duration
    ) {
        $this->handler->extend($message, $adapter, $duration);
    }

    /**
     * @param MessageInterface $message
     * @param AdapterInterface $adapter
     * @param mixed            $result
     */
    protected function reject(
        MessageInterface $message,
        AdapterInterface $adapter,
        $result = null
    ) {
        $this->handler->reject($message, $adapter, $result);
    }

    /**
     * @param AdapterInterface $adapter
     */
    protected function flush(AdapterInterface $adapter)
    {
        $this->handler->flush($adapter);
    }
}
