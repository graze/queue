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
    protected $extra;

    /**
     * @param MessageInterface[]
     */
    protected $messages;

    /**
     * @param string $message
     * @param AdapterInterface $adapter
     * @param MessageInterface[] $messages
     * @param array $extra
     */
    public function __construct($message, AdapterInterface $adapter, array $messages, array $extra = [])
    {
        $this->adapter = $adapter;
        $this->extra = $extra;
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
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
