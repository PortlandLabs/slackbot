<?php
namespace PortlandLabs\Slackbot\Command;

use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class Manager
{

    use LoggerAwareTrait;

    /** @var Command[] */
    protected $commands = [];

    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->setLogger($logger);
    }

    /**
     * Get all commands
     *
     * @return Command[]
     */
    public function all(): iterable
    {
        foreach ($this->commands as $key => $command) {
            if (is_string($command)) {
                $command = $this->container->get($command);
                $this->commands[$key] = $command;
            }

            yield $key => $command;
        }
    }

    /**
     * Add a command to this manager
     *
     * @param Command|string $command
     *
     * @return Manager
     */
    public function addCommand($command): Manager
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
        foreach ($this->all() as $command) {
            if ($command->shouldHandle($message)) {
                $this->runCommand($command, $message);
            }
        }
    }

    /**
     * Run a command
     *
     * @param Command $command
     * @param Message $message
     */
    protected function runCommand(Command $command, Message $message)
    {
        $commandClass = basename(get_class($command));
        $this->logger->debug("[CMD !! ] Matched message to command '$commandClass'");

        $command->handle($message);
    }

}