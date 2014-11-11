<?php
namespace Graze\Queue\Handler;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

class NullAcknowledgementHandler extends AbstractAcknowledgementHandler
{
    /**
     * {@inheritdoc}
     */
    protected function acknowledge(
        MessageInterface $message,
        AdapterInterface $adapter,
        $result = null
    ) {
        // Don't acknowledge
    }

    /**
     * {@inheritdoc}
     */
    protected function flush(AdapterInterface $adapter)
    {
        // Nothing to flush
    }
}
