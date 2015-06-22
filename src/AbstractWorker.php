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

use Closure;
use Graze\Queue\Message\MessageInterface;

abstract class AbstractWorker
{
    /**
     * @param MessageInteface $message
     * @param Closure $done
     */
    abstract protected function execute(MessageInterface $message, Closure $done);

    /**
     * @param MessageInteface $message
     * @param Closure $done
     */
    public function __invoke(MessageInterface $message, Closure $done)
    {
        return $this->execute($message, $done);
    }
}
