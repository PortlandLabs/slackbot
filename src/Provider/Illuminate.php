<?php
namespace PortlandLabs\Slackbot\Provider;

use Illuminate\Container\Container;
use PortlandLabs\Slackbot\Bot;
use Psr\Container\ContainerInterface;

class Illuminate implements Illuminate\Provider
{

    /** @var string[] */
    protected $providers = [
        Illuminate\Slack::class,
        Illuminate\Log::class,
        Illuminate\Command::class,
        Illuminate\Cache::class,
        Illuminate\Filesystem::class,
    ];

    /**
     * Register our individual providers
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container->instance(ContainerInterface::class, $container);
        $container->singleton(Bot::class);

        // Add other providers
        foreach ($this->providers as $provider) {
            $container->make($provider)->register($container);
        }
    }
}