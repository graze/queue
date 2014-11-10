<?php
namespace Graze\Queue\Adapter\Exception;

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
     * @param string $message
     * @param AdapterInterface $adapter
     * @param MessageInterface[] $messages
     * @param array $debug
     */
    public function __construct($message, AdapterInterface $adapter, array $messages, array $debug = [])
    {
        $this->debug = $debug;
        $this->adapter = $adapter;
        $this->messages = $messages;

        parent::__construct($message);
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
