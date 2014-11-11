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
namespace Graze\Queue\Handler;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

class EagerAcknowledgementHandler extends AbstractAcknowledgementHandler
{
    /**
     * {@inheritdoc}
     */
    protected function acknowledge(
        MessageInterface $message,
        AdapterInterface $adapter,
        $result = null
    ) {
        $adapter->acknowledge([$message]);
    }

    /**
     * {@inheritdoc}
     */
    protected function flush(AdapterInterface $adapter)
    {
        // Nothing to flush
    }
}
