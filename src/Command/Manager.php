<?php
namespace PortlandLabs\Slackbot\Command;

use PortlandLabs\Slackbot\Bot;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class Manager
{

    use LoggerAwareTrait;

    /** @var Bot */
    protected $bot;

    /** @var Command[] */
    protected $commands = [];

    /** @var ArgumentManager[] */
    protected $arguments = [];

    /** @var ContainerInterface */
    protected $container;

    public function __construct(Bot $bot, ContainerInterface $container, LoggerInterface $logger)
    {
        $this->bot = $bot;
        $this->container = $container;
        $this->setLogger($logger);
    }

    /**
     * Get all commands
     *
     * @return Command[]
     */
    public function all()
    {
        return $this->commands;
    }

    /**
     * Add a command to this manager
     *
     * @param Command|string $command
     *
     * @return Manager
     */
    public function addCommand(Command $command): Manager
    {
        $this->commands[] = $command;
        return $this;
    }

    /**
     * Handle a message
     *
     * @param Message $message
     */
    public function handle(Message $message)
    {
        foreach ($this->commands as $command) {
            if ($command->shouldHandle($message)) {
                $this->runCommand($command, $message);
            }
        }
    }

    protected function runCommand(Command $command, Message $message)
    {
        $commandClass = basename(get_class($command));
        $this->logger->debug("[CMD !! ] Matched message to command '$commandClass'");

        $command->handle($message);
    }

}