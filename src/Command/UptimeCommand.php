<?php
namespace PortlandLabs\Slackbot\Command;

use PortlandLabs\Slackbot\Command\Argument\Manager;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

class UptimeCommand extends SimpleCommand
{

    protected $description = 'Find out when I woke up';

    protected $signature = 'uptime {--t|timestamp : Show as seconds since I started}';

    /**
     * Handle a message
     *
     * @param Message $message
     * @param Manager $manager
     *
     * @return void
     */
    public function run(Message $message, Manager $manager)
    {
        if ($manager && $manager->get('timestamp')) {
            $start = $this->bot->getConnectedTime();
            $now = new \DateTime();
            $this->bot->feignTyping($message->getChannel(), $now->getTimestamp() - $start->getTimestamp());

            return;
        }

        $this->bot->feignTyping($message->getChannel(), $this->bot->getUptime());
    }
}