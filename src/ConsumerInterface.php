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

use Graze\Queue\Message\MessageInterface;

interface ConsumerInterface
{
    /**
     * @param callable $worker
     * @param integer|null $limit Integer limit or Null no limit
     */
    public function receive(callable $worker, $limit = 1);
}
