<?php
namespace PortlandLabs\Slackbot\Provider\Illuminate;

use Illuminate\Container\Container;
use League\CLImate\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Log implements Provider
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
        // Use the CLImate logger
        $container->bind(LoggerInterface::class, function(Container $container) {
            return $container->make(Logger::class, ['level' => LogLevel::DEBUG]);
        });
    }
}