<?php
namespace PortlandLabs\Slackbot\Command;

use PortlandLabs\Slackbot\Command\Argument\Manager;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

class RoleCommand extends SimpleCommand
{

    protected $description = 'Find out what "Role" you have';

    protected $signature = 'role';

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
        $role = class_basename(get_class($this->checker->getRole($message)));
        $an = in_array($role[0], ['A', 'E', 'I', 'O', 'U']) && $role !== 'User' ? 'an' : 'a';

        $this->bot->feignTyping(
            $message->getChannel(),
            sprintf('You are %s `%s`', $an, $role));
    }
}