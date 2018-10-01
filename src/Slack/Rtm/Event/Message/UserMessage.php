<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

/**
 * Standard user message, no subtype
 */
class UserMessage implements Message
{

    use MessageTrait;
}