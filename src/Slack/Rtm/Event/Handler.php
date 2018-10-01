<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event;

use PortlandLabs\Slackbot\Slack\Rtm\Event;

interface Handler
{

    /**
     * Handle an incoming event
     *
     * @param Event $event
     * @return Event
     */
    public function handle(Event $event): Event;

}