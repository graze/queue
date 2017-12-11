<?php

namespace Graze\Queue\Adapter;

use Graze\Queue\Message\MessageFactoryInterface;
use Graze\Queue\Message\MessageInterface;
use GuzzleHttp\Promise\PromiseInterface;

interface AsyncAdapterInterface
{
    /**
     * @param MessageInterface[] $messages
     *
     * @return PromiseInterface[] List of promises, once for each message
     */
    public function acknowledgeAsync(array $messages);

    /**
     * @param MessageFactoryInterface $factory
     * @param int                     $limit
     * @param callable                $onMessage
     *
     * @return void
     */
    public function dequeueAsync(MessageFactoryInterface $factory, $limit, callable $onMessage);

    /**
     * @param MessageInterface[] $messages
     *
     * @return PromiseInterface[] List of promises, one for each message
     */
    public function enqueueAsync(array $messages);

    /**
     * @return PromiseInterface
     */
    public function purgeAsync();

    /**
     * @return PromiseInterface
     */
    public function deleteAsync();
}
