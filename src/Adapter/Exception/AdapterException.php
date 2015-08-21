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

namespace Graze\Queue\Adapter\Exception;

use Exception;
use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;
use RuntimeException;

class AdapterException extends RuntimeException
{
    /**
     * @param AdapterInterface
     */
    protected $adapter;

    /**
     * @param array
     */
    protected $debug;

    /**
     * @param MessageInterface[]
     */
    protected $messages;

    /**
     * @param string             $message
     * @param AdapterInterface   $adapter
     * @param MessageInterface[] $messages
     * @param array              $debug
     * @param Exception          $previous
     */
    public function __construct($message, AdapterInterface $adapter, array $messages, array $debug = [], Exception $previous = null)
    {
        $this->debug = $debug;
        $this->adapter = $adapter;
        $this->messages = $messages;

        parent::__construct($message, 0, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
