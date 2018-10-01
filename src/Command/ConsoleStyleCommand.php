<?php
namespace PortlandLabs\Slackbot\Command;

use PortlandLabs\Slackbot\Command\Argument\Manager;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

/**
 * A command that acts similarly to a console command
 * @example @somebot do-something --with="test"
 */
interface ConsoleStyleCommand extends Command
{

    /**
     * Add arguments to the argument manager
     * This method gets called after we match the command to a request
     *
     * @param Manager $manager
     *
     * @return Manager
     */
    public function configure(Manager $manager): Manager;

    /**
     * Handle a message
     *
     * @param Message $message
     * @param Manager $manager
     *
     * @return void
     */
    public function run(Message $message, Manager $manager);

    /**
     * Get the description for this command
     *
     * @return string
     */
    public function getDescription(): string;

}