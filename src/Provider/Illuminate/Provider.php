<?php
namespace PortlandLabs\Slackbot\Provider\Illuminate;

use Illuminate\Container\Container;

interface Provider
{

    /**
     * Register this provider
     *
     * @param Container $container
     *
     * @return void
     */
    public function register(Container $container);

}