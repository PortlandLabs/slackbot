<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event;

use PortlandLabs\Slackbot\Slack\Rtm\Event;

interface Message extends Event
{

    /**
     * Get the subtype of the message
     *
     * @see https://api.slack.com/events/message for subtypes
     *
     * @return null|string
     */
    public function getSubtype(): ?string;

    /**
     * Get the timestamp associated with the message
     *
     * @return string
     */
    public function getTimestamp(): string;

    /**
     * Get the user associated with the message
     *
     * @return null|string
     */
    public function getUser(): ?string;

    /**
     * Get the channel associated with the message
     *
     * @return null|string
     */
    public function getChannel(): ?string;

    /**
     * Get the actual message text
     *
     * @return null|string
     */
    public function getText(): ?string;

}