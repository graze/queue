<?php

/*
 * This file is part of Graze Queue
 *
 * Copyright (c) 2014 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see  http://github.com/graze/queue/blob/master/LICENSE
 * @link http://github.com/graze/queue
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
     * @return boolean
     */
    public function isValid();
}
