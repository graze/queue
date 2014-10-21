<?php
namespace Graze\Queue\Adapter\Exception;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

class FailedAcknowledgementException extends AdapterException
{
    /**
     * @param AdapterInterface $adapter
     * @param MessageInterface[] $messages
     * @param array $extra
     */
    public function __construct(AdapterInterface $adapter, array $messages, array $extra = [])
    {
        parent::__construct('Failed to acknowledge messages', $adapter, $messages, $extra);
    }
}
