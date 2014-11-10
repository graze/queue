<?php
namespace Graze\Queue\Message;

use Graze\DataStructure\Container\ContainerInterface;

interface MessageInterface
{
    /**
     * @return string
     */
    public function getBody();

    /**
     * @return ContainerInterface
     */
    public function getMetadata();

    /**
     * @return boolean
     */
    public function isValid();
}
