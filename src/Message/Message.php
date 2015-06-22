<?php

/**
 * This file is part of graze/queue.
 *
 * Copyright (c) 2015 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/queue/blob/master/LICENSE MIT
 * @link https://github.com/graze/queue
 */

namespace Graze\Queue\Message;

use Graze\DataStructure\Container\ContainerInterface;

final class Message implements MessageInterface
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
     * @param ContainerInterface $metadata
     * @param callable $validator
     */
    public function __construct($body, ContainerInterface $metadata, callable $validator)
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
