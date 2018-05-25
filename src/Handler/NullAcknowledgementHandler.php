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

class NullAcknowledgementHandler extends AbstractAcknowledgementHandler
{
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
        // Don't acknowledge.
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
        // Don't delay
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
        // Don't reject
    }

    /**
     * @param AdapterInterface $adapter
     */
    protected function flush(AdapterInterface $adapter)
    {
        // Nothing to flush.
    }
}
