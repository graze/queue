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

namespace Graze\Queue;

use Graze\Queue\Message\MessageInterface;

interface ProducerInterface
{
    /**
     * @return MessageInterface
     */
    public function create($body, array $options = []);

    /**
     * @param MessageInterface[] $message
     */
    public function send(array $messages);
}
