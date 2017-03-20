<?php

/**
 * This file is part of graze/queue.
 *
 * Copyright (c) 2017 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/queue/blob/master/LICENSE MIT
 *
 * @link    https://github.com/graze/queue
 */

namespace Graze\Queue\Adapter;

/**
 * Attached to adapters that may be name-able, such as an SQS queue name.
 * This is useful in some core pieces of code, especially when throwing useful exceptions.
 */
interface NamedInterface
{
    /**
     * @return string
     */
    public function getQueueName();
}
