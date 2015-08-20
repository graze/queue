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

namespace Graze\Queue;

use Graze\Queue\Adapter\Exception\UnsupportedOperationException;
use Graze\Queue\Message\MessageInterface;

interface ProducerInterface
{
    /**
     * @param string $body
     * @param array  $options
     */
    public function create($body, array $options = []);

    /**
     * @param array $message
     */
    public function send(array $messages);

    public function purge();
}
