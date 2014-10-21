<?php
namespace Graze\Queue;

use Graze\Queue\Message\MessageInterface;

interface ProducerInterface
{
    /**
     * @return MessageInterface
     */
    public function create($body, array $options = []);

    /**
     * @param MessageInterface[] $message
     */
    public function send(array $messages);
}
