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

namespace Graze\Queue\Handler;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

class ResultAcknowledgementHandler extends AbstractAcknowledgementHandler
{
    /**
     * @var AbstractAcknowledgementHandler
     */
    private $passThrough;

    /**
     * @var callable
     */
    private $isValid;

    /**
     * ResultAcknowledgementHandler constructor.
     *
     * @param callable                       $isValid
     * @param AbstractAcknowledgementHandler $passThrough
     */
    public function __construct(callable $isValid, AbstractAcknowledgementHandler $passThrough = null)
    {
        $this->isValid = $isValid;
        $this->passThrough = $passThrough ?: new EagerAcknowledgementHandler();
    }

    /**
     * {@inheritdoc}
     */
    protected function acknowledge(
        MessageInterface $message,
        AdapterInterface $adapter,
        $result = null
    ) {
        if (call_user_func($this->isValid, $result)) {
            $this->passThrough->acknowledge($message, $adapter, $result);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function flush(AdapterInterface $adapter)
    {
        $this->passThrough->flush($adapter);
    }
}
