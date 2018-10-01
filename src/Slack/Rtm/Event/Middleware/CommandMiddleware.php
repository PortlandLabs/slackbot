<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event\Middleware;

use PortlandLabs\Slackbot\Command\Manager;
use PortlandLabs\Slackbot\Slack\Api\Client;
use PortlandLabs\Slackbot\Slack\Rtm\Event;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Handler;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Middleware;

class CommandMiddleware implements Middleware
{

    /** @var Client */
    protected $api;

    /** @var Manager */
    protected $commands;

    public function __construct(Client $apiClient, Manager $manager)
    {
        $this->api = $apiClient;
        $this->commands = $manager;
    }

    /**
     * Process an incoming event modifying it as needed
     * The returned event should optionally be passed to the next $handler
     *
     * @param Event $event
     * @param Handler $handler
     * @return Event
     */
    public function process(Event $event, Handler $handler): Event
    {
        if ($event instanceof Event\Message) {
            $this->commands->handle($event);
        }

        return $handler->handle($event);
    }
}