<?php
namespace Graze\Queue\Message;

interface MessageFactoryInterface
{
    /**
     * @param string $body
     * @param array $options
     * @return MessageInterface
     */
    public function createMessage($body, array $options = []);
}
