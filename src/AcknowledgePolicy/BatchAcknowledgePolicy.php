<?php
namespace Graze\Queue\AcknowledgePolicy;

use Graze\Queue\Adapter\AdapterInterface;
use Graze\Queue\Message\MessageInterface;

class BatchAcknowledgePolicy implements AcknowledgePolicyInterface
{
    /**
     * @var integer
     */
    protected $batchSize;

    /**
     * @var MessageInterface[]
     */
    protected $messages = [];

    /**
     * @param integer $batchSize
     */
    public function __construct($batchSize = null)
    {
        $this->batchSize = (integer) $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(MessageInterface $message, AdapterInterface $adapter, $result = null)
    {
        $this->messages[] = $message;

        if (count($this->messages) === $this->batchSize) {
            $this->flush($adapter);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush(AdapterInterface $adapter)
    {
        if (!empty($this->messages)) {
            $adapter->acknowledge($this->messages);

            $this->messages = [];
        }
    }
}
