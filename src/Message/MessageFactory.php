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

use Graze\DataStructure\Container\ImmutableContainer;

class MessageFactory implements MessageFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createMessage($body, array $options = [])
    {
        return new Message($body, $this->getMetadata($options), $this->getValidator($options));
    }

    /**
     * @return callable
     */
    protected function getMetadata(array $options)
    {
        $metadata = isset($options['metadata']) ? $options['metadata'] : [];

        return new ImmutableContainer($metadata);
    }

    /**
     * @return callable
     */
    protected function getValidator(array $options)
    {
        return isset($options['validator']) ? $options['validator'] : function (MessageInterface $message) {
            return true;
        };
    }
}
