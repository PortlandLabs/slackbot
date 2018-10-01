<?php
namespace PortlandLabs\Slackbot\Command;

use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

interface Command
{

    /**
     * Determine whether this command should handle a message
     *
     * @param Message $message
     *
     * @return bool
     */
    public function shouldHandle(Message $message): bool;

    /**
     * Handle a message
     *
     * @param Message $message
     *
     * @return void
     */
    public function handle(Message $message);

}
