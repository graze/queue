<?php
namespace Graze\Queue\Adapter;

use ArrayIterator;
use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use LimitIterator;

class ArrayAdapter implements AdapterInterface
{
    /**
     * @param MessageInterface[]
     */
    protected $queue;

    /**
     * @param MessageInterface[] $messages
     */
    public function __construct(array $messages = [])
    {
        $this->enqueue($messages);
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(array $messages)
    {
        $this->queue = array_values(array_filter($this->queue, function($message) use ($messages) {
            return false === array_search($message, $messages, true);
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue(MessageFactoryInterface $factory, $limit)
    {
        return new LimitIterator(new ArrayIterator($this->queue), 0, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(array $messages)
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }
    }

    /**
     * @param MessageInterface $message
     */
    protected function addMessage(MessageInterface $message)
    {
        $this->queue[] = $message;
    }
}
