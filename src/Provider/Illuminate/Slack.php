<?php


namespace PortlandLabs\Slackbot\Provider\Illuminate;

use CL\Slack\Serializer\PayloadResponseSerializer;
use CL\Slack\Serializer\PayloadSerializer;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use Illuminate\Container\Container;
use PortlandLabs\Slackbot\Slack\Api\Client as ApiClient;
use PortlandLabs\Slackbot\Slack\Api\Payload\Serializer;
use PortlandLabs\Slackbot\Slack\Api\Payload\ResponseSerializer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Slack implements Provider
{

    /**
     * Register this provider
     *
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container->when(ApiClient::class)->needs('$token')->give(getenv('AUTH_TOKEN'));
        $container->when(ApiClient::class)->needs(PayloadSerializer::class)->give(Serializer::class);
        $container->when(ApiClient::class)->needs(PayloadResponseSerializer::class)->give(ResponseSerializer::class);

        $container->bind(ClientInterface::class, HttpClient::class);
        $container->bind(EventDispatcherInterface::class, EventDispatcher::class);
    }
}