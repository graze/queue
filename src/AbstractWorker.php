<?php
/*
 * This file is part of Graze Queue
 *
 * Copyright (c) 2014 Nature Delivered Ltd. <http://graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see  http://github.com/graze/queue/blob/master/LICENSE
 * @link http://github.com/graze/queue
 */
namespace Graze\Queue;

use Closure;
use Graze\Queue\Message\MessageInterface;

abstract class AbstractWorker
{
    /**
     * @param MessageFactoryInteface $factory
     * @param Closure $done
     */
    abstract protected function execute(MessageInterface $message, Closure $done);

    /**
     * @param MessageFactoryInteface $factory
     * @param Closure $done
     */
    public function __invoke(MessageInterface $message, Closure $done)
    {
        return $this->execute($message, $done);
    }
}
