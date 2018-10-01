<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event;

use PortlandLabs\Slackbot\Slack\Rtm\Event;

interface Middleware
{

    /**
     * Process an incoming event modifying it as needed
     * The returned event should optionally be passed to the next $handler
     *
     * @param Event $event
     * @param Handler $handler
     * @return Event
     */
    public function process(Event $event, Handler $handler): Event;

}