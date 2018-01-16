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

class BatchAcknowledgementHandler extends AbstractAcknowledgementHandler
{
    /** @var int */
    protected $batchSize;

    /** @var MessageInterface[] */
    protected $acknowledged = [];

    /** @var MessageInterface[] */
    protected $rejected = [];

    /**
     * @param int $batchSize
     */
    public function __construct($batchSize = 0)
    {
        $this->batchSize = (integer) $batchSize;
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
        $this->acknowledged[] = $message;

        if (count($this->acknowledged) === $this->batchSize) {
            $this->flush($adapter);
        }
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
        $this->rejected[] = $message;

        if (count($this->rejected) === $this->batchSize) {
            $this->flush($adapter);
        }
    }

    /**
     * @param AdapterInterface $adapter
     */
    protected function flush(AdapterInterface $adapter)
    {
        if (!empty($this->acknowledged)) {
            $adapter->acknowledge($this->acknowledged);

            $this->acknowledged = [];
        }
        if (!empty($this->rejected)) {
            $adapter->acknowledge($this->rejected);

            $this->rejected = [];
        }
    }
}
