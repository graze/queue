<?php
namespace Graze\Queue;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

abstract class AbstractWorker
{
    /**
     * @param MessageFactoryInteface $factory
     * @param AdapterInterface $adapter
     */
    abstract protected function execute(MessageInterface $message, AdapterInterface $adapter);

    /**
     * @param MessageFactoryInteface $factory
     * @param AdapterInterface $adapter
     */
    public function __invoke(MessageInterface $message, AdapterInterface $adapter)
    {
        return $this->execute($message, $adapter);
    }
}
