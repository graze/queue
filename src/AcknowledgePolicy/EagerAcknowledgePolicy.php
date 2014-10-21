<?php
namespace Graze\Queue\AcknowledgePolicy;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

class EagerAcknowledgePolicy implements AcknowledgePolicyInterface
{
    /**
     * {@inheritdoc}
     */
    public function acknowledge(MessageInterface $message, AdapterInterface $adapter, $result = null)
    {
        $adapter->acknowledge([$message]);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(AdapterInterface $adapter)
    {
    }
}
