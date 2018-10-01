<?php
namespace PortlandLabs\Slackbot;

use Illuminate\Container\Container as IlluminateContainer;

class Container extends IlluminateContainer
{

    /**
     * Override the ->get to allow autowiring to work in PSR-11
     *
     * @param $abstract
     * @return mixed
     */
    public function get($abstract)
    {
        return $this->resolve($abstract);
    }
}