<?php
namespace Graze\Queue\Message;

interface MessageInterface
{
    /**
     * @return string
     */
    public function getBody();

    /**
     * @return array
     */
    public function getMetadata();

    /**
     * @return boolean
     */
    public function isValid();
}
