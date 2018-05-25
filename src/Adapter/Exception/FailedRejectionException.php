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

namespace Graze\Queue\Adapter\Exception;

use Exception;
use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

/**
 * Exception to throw when a {@see \Graze\Queue\Handler} is unable to reject a message.
 */
class FailedRejectionException extends AdapterException
{
    /**
     * @param AdapterInterface   $adapter
     * @param MessageInterface[] $messages
     * @param array              $debug
     * @param Exception          $previous
     */
    public function __construct(
        AdapterInterface $adapter,
        array $messages,
        array $debug = [],
        Exception $previous = null
    ) {
        parent::__construct('Failed to reject the messages', $adapter, $messages, $debug, $previous);
    }
}
