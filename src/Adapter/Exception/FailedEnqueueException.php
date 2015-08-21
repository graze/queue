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

namespace Graze\Queue\Adapter\Exception;

use Exception;
use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

/**
 * Exception to throw when an {@see \Graze\Queue\Adapter} fails to enqueue a message.
 */
class FailedEnqueueException extends AdapterException
{
    /**
     * @param AdapterInterface   $adapter
     * @param MessageInterface[] $messages
     * @param array              $debug
     * @param Exception          $previous
     */
    public function __construct(AdapterInterface $adapter, array $messages, array $debug = [], Exception $previous = null)
    {
        parent::__construct('Failed to enqueue messages', $adapter, $messages, $debug, $previous);
    }
}
