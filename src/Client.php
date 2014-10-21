<?php
namespace Graze\Queue;

use Exception;
use Graze\Queue\AcknowledgePolicy\AcknowledgePolicyInterface;
use Graze\Queue\AcknowledgePolicy\BatchAcknowledgePolicy;
use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageFactory;
use Graze\Queue\Message\MessageFactoryInterface;

class Client implements ConsumerInterface, ProducerInterface
{
    /**
     * @param AdapterInterface
     */
    protected $adapter;

    /**
     * @param MessageFactoryInterface
     */
    protected $factory;

    /**
     * @param AcknowledgePolicyInterface
     */
    protected $policy;

    /**
     * @param AdapterInterface $adapter
     * @param AcknowledgePolicyInterface $policy
     * @param MessageFactoryInteface $factory
     */
    public function __construct(
        AdapterInterface $adapter,
        AcknowledgePolicyInterface $policy = null,
        MessageFactoryInterface $factory = null
    ) {
        $this->adapter = $adapter;
        $this->factory = $factory ?: $this->createDefaultMessageFactory();
        $this->policy = $policy ?: $this->createDefaultAcknowledgePolicy();
    }

    /**
     * {@inheritdoc}
     */
    public function create($body, array $options = [])
    {
        return $this->factory->createMessage($body, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function receive(callable $worker, $limit = 1)
    {
        $messages = $this->adapter->dequeue($this->factory, $limit);

        try {
            foreach ($messages as $message) {
                if ($message->isValid()) {
                    $result = call_user_func($worker, $message, $this->adapter);
                    $this->policy->acknowledge($message, $this->adapter, $result);
                }
            }
        } catch (Exception $e) {
            $this->policy->flush($this->adapter);
            throw $e;
        }

        $this->policy->flush($this->adapter);
    }

    /**
     * {@inheritdoc}
     */
    public function send(array $messages)
    {
        return $this->adapter->enqueue($messages);
    }

    /**
     * @return AcknowledgePolicyInterface
     */
    protected function createDefaultAcknowledgePolicy()
    {
        return new BatchAcknowledgePolicy();
    }

    /**
     * @return MessageFactoryInterface
     */
    protected function createDefaultMessageFactory()
    {
        return new MessageFactory();
    }
}
