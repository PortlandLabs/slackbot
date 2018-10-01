<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event\Handler;

use PortlandLabs\Slackbot\Slack\Rtm\Event;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Handler;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Middleware;

class Delegate implements Handler
{

    /** @var Middleware */
    protected $nextMiddleware;

    /** @var Handler */
    protected $nextHandler;

    public function __construct(Middleware $nextMiddleware, Handler $nextHandler)
    {
        $this->nextMiddleware = $nextMiddleware;
        $this->nextHandler = $nextHandler;
    }

    /**
     * Handle an incoming event
     *
     * @param Event $event
     * @return Event
     */
    public function handle(Event $event): Event
    {
        return $this->nextMiddleware->process($event, $this->nextHandler);
    }
}