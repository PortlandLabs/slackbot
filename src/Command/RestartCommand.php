<?php
namespace PortlandLabs\Slackbot\Command;

use PortlandLabs\Slackbot\Bot;
use PortlandLabs\Slackbot\Command\Argument\Manager;
use PortlandLabs\Slackbot\Permission\Admin;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

class RestartCommand extends SimpleCommand
{

    protected $description = 'Cause the bot to shut down';

    protected $signature = 'restart {botID? : The ID of the bot to restart}';

    protected $role = Admin::class;

    /**
     * @param Message $message
     * @param Manager $arguments
     * @param Bot $bot
     *
     * @throws \CL\Slack\Exception\SlackException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function run(Message $message, Manager $arguments)
    {
        // If an id is specified
        $passedId = $arguments->get('botID');
        if ($passedId && $passedId !== $this->bot->getId()) {
            return;
        }

        $text = sprintf('Okay *' . $this->bot->getId() . '* is restarting, hold on... _I was up for %s_', $this->bot->getUptime());

        // Stop after a few seconds for sure
        $this->bot->getLoop()->addTimer(2, function() {
            $this->stop();
        });

        // Stop once we get confirmation that our message made it
        $this->bot->feignTyping($message->getChannel(), $text)->done(function() {
            $this->stop();
        });
    }

    protected function stop()
    {
        $this->bot->logger()->debug('[CMD.RST] Shutting down Bot...');
        $this->bot->stop();
    }
}
