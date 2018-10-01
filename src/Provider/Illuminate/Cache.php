<?php
namespace PortlandLabs\Slackbot\Provider\Illuminate;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use Illuminate\Container\Container;
use Psr\SimpleCache\CacheInterface;

class Cache implements Provider
{

    /**
     * Register this provider
     *
     * @param Container $container
     *
     * @return void
     */
    public function register(Container $container)
    {
        $container->singleton(CacheInterface::class, FilesystemCachePool::class);
        $container->when(FilesystemCachePool::class)->needs('$folder')->give('cache');
    }
}