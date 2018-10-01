<?php
namespace PortlandLabs\Slackbot;

use Psr\Container\ContainerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class ContainerFactory
{

    /**
     * Create a new instance of the slackbot's container
     * Use `$container->get(Bot::class)` to get an instance of the bot
     *
     * @param LoopInterface|null $loop
     * @return ContainerInterface
     */
    public static function illuminate(LoopInterface $loop = null): ContainerInterface
    {
        $container = new Container();
        $container->make(Provider\Illuminate::class)->register($container);

        $loop = $loop ?? Factory::create();
        $container->instance(LoopInterface::class, $loop);

        return $container;
    }

}