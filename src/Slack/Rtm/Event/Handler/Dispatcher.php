<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event\Handler;

use PortlandLabs\Slackbot\Slack\Rtm\Event;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Handler;

class Dispatcher implements Handler
{

    /** @var callable */
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Handle an incoming event
     *
     * @param Event $event
     * @return Event
     */
    public function handle(Event $event): Event
    {
        $callback = $this->callback;
        $callback($event);
        return $event;
    }
}