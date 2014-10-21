<?php
namespace Graze\Queue;

use Graze\Queue\Message\MessageInterface;

interface ConsumerInterface
{
    /**
     * @param callable $worker
     * @param integer $limit
     */
    public function receive(callable $worker, $limit = 1);
}
