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
     * @return bool
     */
    public function isValid();
}
