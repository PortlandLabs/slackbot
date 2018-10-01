<?php
namespace PortlandLabs\Slackbot\Command;

use League\CLImate\Argument\Filter;
use League\CLImate\Argument\Summary;
use PortlandLabs\Slackbot\Bot;
use PortlandLabs\Slackbot\Command\Argument\Manager as ArgumentManager;
use PortlandLabs\Slackbot\Permission\Checker;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;
use Psr\Container\ContainerInterface;

class HelpCommand extends SimpleCommand
{

    protected $signature = 'help {command? : The command to get help with}';

    protected $container;

    public function __construct(Bot $bot, ArgumentManager $argumentManager, Checker $checker, ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($bot, $argumentManager, $checker);
    }

    /**
     * Handle a message
     *
     * @param Message $message
     * @param Manager $manager
     *
     * @return void
     */
    public function run(Message $message, ArgumentManager $manager)
    {
        $output = [];

        $filter = new Filter();
        $summary = new Summary();

        $userRole = $this->checker->getRole($message);

        $commandManager = $this->container->get(Manager::class);
        $commandName = $manager->get('command');

        $output[] = 'You can use the following commands:';

        foreach ($commandManager->all() as $command) {
            if (!$command instanceof ConsoleStyleCommand) {
                continue;
            }

            $arguments = new ArgumentManager();
            $arguments = $command->configure($arguments);
            $commandRole = $arguments->getRole();

            if (!$userRole instanceof $commandRole) {
                continue;
            }

            if ($commandName && $arguments->getCommand() === $commandName) {
                $this->outputUsage($message, $arguments);
                return;
            }

            $filter->setArguments($arguments->all());

            // Output the simple usage statement
            $orderedArguments = array_merge($filter->withPrefix(), $filter->withoutPrefix());
            $short = $summary->short($orderedArguments);
            $output[] = "> • `{$arguments->getCommand()}" . ($short ? " {$short}`" : '`');
        }

        $this->bot->feignTyping($message->getChannel(), implode("\n", $output));
    }
}