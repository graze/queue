<?php
namespace Graze\Queue\AcknowledgePolicy;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

interface AcknowledgePolicyInterface
{
    /**
     * @param MessageInterface $message
     * @param AdapterInterface $adapter
     * @param mixed $result
     */
    public function acknowledge(MessageInterface $message, AdapterInterface $adapter, $result = null);

    /**
     * @param AdapterInterface $adapter
     */
    public function flush(AdapterInterface $adapter);
}
