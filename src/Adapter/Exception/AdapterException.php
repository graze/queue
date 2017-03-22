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

namespace Graze\Queue\Adapter\Exception;

use Exception;
use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Adapter\NamedInterface;
use Graze\Queue\Message\MessageInterface;
use RuntimeException;

class AdapterException extends RuntimeException
{
    /** @var AdapterInterface */
    protected $adapter;
    /** @var array */
    protected $debug;
    /** @var MessageInterface[] */
    protected $messages;
    /** @var string|null */
    protected $queueName;

    /**
     * @param string             $message
     * @param AdapterInterface   $adapter
     * @param MessageInterface[] $messages
     * @param array              $debug
     * @param Exception          $previous
     */
    public function __construct(
        $message,
        AdapterInterface $adapter,
        array $messages,
        array $debug = [],
        Exception $previous = null
    ) {
        $this->debug = $debug;
        $this->adapter = $adapter;
        $this->messages = $messages;

        if ($adapter instanceof NamedInterface) {
            $this->queueName = $adapter->getQueueName();
        }

        parent::__construct($this->queueName . ': ' . $message, 0, $previous);
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return array
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @return \Graze\Queue\Message\MessageInterface[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return null|string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }
}
