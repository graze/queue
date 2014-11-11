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

class BatchAcknowledgementHandler extends AbstractAcknowledgementHandler
{
    /**
     * @var integer
     */
    protected $batchSize;

    /**
     * @var MessageInterface[]
     */
    protected $messages = [];

    /**
     * @param integer $batchSize
     */
    public function __construct($batchSize = 0)
    {
        $this->batchSize = (integer) $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    protected function acknowledge(
        MessageInterface $message,
        AdapterInterface $adapter,
        $result = null
    ) {
        $this->messages[] = $message;

        if (count($this->messages) === $this->batchSize) {
            $this->flush($adapter);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function flush(AdapterInterface $adapter)
    {
        if (!empty($this->messages)) {
            $adapter->acknowledge($this->messages);

            $this->messages = [];
        }
    }
}
