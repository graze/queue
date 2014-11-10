<?php
namespace Graze\Queue\Handler;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

abstract class AbstractAcknowledgementHandler
{
    /**
     * @param MessageInterface $message
     * @param AdapterInterface $adapter
     * @param mixed $result
     */
    abstract protected function acknowledge(
        MessageInterface $message,
        AdapterInterface $adapter,
        $result = null
    );

    /**
     * @param AdapterInterface $adapter
     */
    abstract protected function flush(AdapterInterface $adapter);

    /**
     * @param MessageInterface[] $messages
     * @param AdapterInterface $adapter
     * @param callable $worker
     */
    public function __invoke(array $messages, AdapterInterface $adapter, callable $worker)
    {
        try {
            foreach ($messages as $message) {
                if ($message->isValid()) {
                    $result = call_user_func($worker, $message, $adapter);
                    $this->acknowledge($message, $adapter, $result);
                }
            }
        } catch (Exception $e) {
            $this->flush($adapter);
            throw $e;
        }

        $this->flush($adapter);
    }
}
