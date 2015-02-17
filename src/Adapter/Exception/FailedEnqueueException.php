<?php

/*
 * This file is part of Graze Queue
 *
 * Copyright (c) 2014 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see  http://github.com/graze/queue/blob/master/LICENSE
 * @link http://github.com/graze/queue
 */

namespace Graze\Queue\Adapter\Exception;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

class FailedEnqueueException extends AdapterException
{
    /**
     * @param AdapterInterface $adapter
     * @param MessageInterface[] $messages
     * @param array $debug
     */
    public function __construct(AdapterInterface $adapter, array $messages, array $debug = [])
    {
        parent::__construct('Failed to enqueue messages', $adapter, $messages, $debug);
    }
}
