<?php
namespace Graze\Queue;

use Closure;
use Graze\Queue\Message\MessageInterface;

abstract class AbstractWorker
{
    /**
     * @param MessageFactoryInteface $factory
     * @param Closure $done
     */
    abstract protected function execute(MessageInterface $message, Closure $done);

    /**
     * @param MessageFactoryInteface $factory
     * @param Closure $done
     */
    public function __invoke(MessageInterface $message, Closure $done)
    {
        return $this->execute($message, $done);
    }
}
