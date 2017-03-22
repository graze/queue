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
 *
 * @link    https://github.com/graze/queue
 */

namespace Graze\Queue\Message;

use Graze\DataStructure\Container\ImmutableContainer;

final class MessageFactory implements MessageFactoryInterface
{
    /**
     * @param string $body
     * @param array  $options
     *
     * @return Message
     */
    public function createMessage($body, array $options = [])
    {
        return new Message($body, $this->getMetadata($options), $this->getValidator($options));
    }

    /**
     * @param array $options
     *
     * @return ImmutableContainer
     */
    protected function getMetadata(array $options)
    {
        $metadata = isset($options['metadata']) ? $options['metadata'] : [];

        return new ImmutableContainer($metadata);
    }

    /**
     * @param array $options
     *
     * @return \Closure
     */
    protected function getValidator(array $options)
    {
        return isset($options['validator']) ? $options['validator'] : function () {
            return true;
        };
    }
}
