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

class EagerAcknowledgementHandler extends AbstractAcknowledgementHandler
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
        $adapter->acknowledge([$message]);
    }

    /**
     * @param AdapterInterface $adapter
     */
    protected function flush(AdapterInterface $adapter)
    {
        // Nothing to flush
    }
}
