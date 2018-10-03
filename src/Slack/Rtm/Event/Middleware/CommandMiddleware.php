<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event\Middleware;

use PortlandLabs\Slackbot\Bot;
use PortlandLabs\Slackbot\Slack\Api\Client;
use PortlandLabs\Slackbot\Slack\Rtm\Event;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Handler;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Middleware;

class CommandMiddleware implements Middleware
{

    /** @var Client */
    protected $bot;

    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
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
            $this->bot->commands()->handle($event);
        }

        return $handler->handle($event);
    }
}