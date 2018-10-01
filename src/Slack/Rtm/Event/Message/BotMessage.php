<?php


namespace PortlandLabs\Slackbot\Slack\Rtm\Event\Message;


use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

class BotMessage implements Message
{

    use MessageTrait;

    protected $username;

}