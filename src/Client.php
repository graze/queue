<?php
namespace Graze\Queue;

use Exception;
use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Handler\BatchAcknowledgementHandler;
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
     * @param callable
     */
    protected $handler;

    /**
     * @param AdapterInterface $adapter
     * @param array $config
     *     - handler <callable> Handler to apply a worker to a list of messages
     *       and determine when to send acknowledgement.
     *     - message_factory <MessageFactoryInterface> Factory used to create
     *       messages.
     */
    public function __construct(AdapterInterface $adapter, array $config = [])
    {
        $this->adapter = $adapter;

        $this->handler = isset($config['handler'])
            ? $config['handler']
            : $this->createDefaultHandler();

        $this->factory = isset($config['message_factory'])
            ? $config['message_factory']
            : $this->createDefaultMessageFactory();
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

        call_user_func($this->handler, $messages, $this->adapter, $worker);
    }

    /**
     * {@inheritdoc}
     */
    public function send(array $messages)
    {
        return $this->adapter->enqueue($messages);
    }

    /**
     * @return callable
     */
    protected function createDefaultHandler()
    {
        return new BatchAcknowledgementHandler();
    }

    /**
     * @return MessageFactoryInterface
     */
    protected function createDefaultMessageFactory()
    {
        return new MessageFactory();
    }
}
