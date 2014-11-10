<?php
namespace Graze\Queue\Message;

use Graze\DataStructure\Container\ContainerInterface;

class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $body;

    /**
     * @var ContainerInterface
     */
    protected $metadata;

    /**
     * @var callable
     */
    protected $validator;

    /**
     * @param string $body
     * @param callable $validator
     * @param ContainerInterface $metadata
     */
    public function __construct($body, callable $validator, ContainerInterface $metadata)
    {
        $this->body = (string) $body;
        $this->metadata = $metadata;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return (boolean) call_user_func($this->validator, $this);
    }
}
