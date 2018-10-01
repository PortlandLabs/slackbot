<?php


namespace PortlandLabs\Slackbot\Provider\Illuminate;


use Illuminate\Container\Container;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem as Flysystem;

class Filesystem implements Provider
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
        $container->when(Flysystem::class)->needs(AdapterInterface::class)->give(function(Container $container) {
            return $container->make(Local::class, ['root' => __DIR__ . '/../../../']);
        });
    }
}