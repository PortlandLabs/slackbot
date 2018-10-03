<?php
namespace PortlandLabs\Slackbot\Command;

use Illuminate\Support\Str;
use League\CLImate\Argument\Argument;
use League\CLImate\Argument\Filter;
use League\CLImate\Argument\Summary;
use PortlandLabs\Slackbot\Bot;
use PortlandLabs\Slackbot\Command\Argument\Manager;
use PortlandLabs\Slackbot\Permission\Checker;
use PortlandLabs\Slackbot\Permission\User;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

abstract class SimpleCommand implements ConsoleStyleCommand
{

    /** @var string */
    protected $signature = '';

    /** @var string */
    protected $description = '';

    /** @var Bot */
    protected $bot;

    /** @var Manager */
    protected $argumentManager;

    /** @var Checker */
    protected $checker;

    /** @var string The Role class required */
    protected $role = User::class;

    public function __construct(Bot $bot, Manager $argumentManager, Checker $checker)
    {
        $this->bot = $bot;
        $this->argumentManager = $argumentManager;
        $this->checker = $checker;
    }

    /**
     * Add arguments to the argument manager
     * This method gets called after we match the command to a request
     *
     * @param Manager $manager
     *
     * @return Manager
     * @throws \Exception
     */
    public function configure(Manager $manager): Manager
    {
        $manager = SignatureParser::parse($this->signature, $manager);
        $manager->setRole($this->role);

        return $manager;
    }

    /**
     * Determine whether we should handle this message
     *
     * @param Message $message
     *
     * @return bool
     * @throws \Exception
     */
    public function shouldHandle(Message $message): bool
    {
        $text = $message->getText();
        $userId = $this->bot->rtm()->getUserId();

        // Early return if the text doesn't start with "@slackbot "
        if (!Str::startsWith($text, "<@$userId>")) {
            return false;
        }

        // Determine our signature
        $command = $this->argumentManager->getCommand();
        if (!$command) {
            $this->argumentManager = $this->configure($this->argumentManager);
            $command = $this->argumentManager->getCommand();
        }

        // If we still don't have a command, let's just return false
        if (!$command) {
            return false;
        }

        $shouldRun = Str::startsWith($text, "<@$userId> $command ") || $text === "<@$userId> $command";
        if (!$shouldRun) {
            return false;
        }

        // Make sure the user have the right role
        $role = $this->checker->getRole($message);
        return ($role instanceof $this->role);
    }

    /**
     * Handle a simple command message
     *
     * @param Message $message
     * @throws \Exception
     */
    public function handle(Message $message)
    {
        $argumentManager = $this->argumentManager;
        if (!$argumentManager->getCommand()) {
            $argumentManager = $this->configure($argumentManager);
        }

        if (!$argumentManager->exists('help')) {
            $argumentManager->add([
                'help' => [
                    'prefix' => 'h',
                    'longPrefix' => 'help',
                    'description' => 'Output helpful information about a command',
                    'noValue' => true
                ]
            ]);
        }

        // Parse the actual message text
        $text = Str::replaceFirst(
            sprintf('<@%s> ', $this->bot->rtm()->getUserId()),
            '',
            $message->getText());

        // Pass in the actual split string
        $parsedManager = clone $argumentManager;

        // GT / LT signs are encoded in slack messages so we can safely use them here as a stand-in.
        $argv = \Clue\Arguments\split(str_replace("\n", "<>\n", $text));

        // Restore newlines
        $argv = array_map(function($arg) {
            return str_replace('<>', "\n", $arg);
        }, $argv);

        try {
            $parsedManager->parse($argv);
        } catch (\Exception $e) {
            if (!$parsedManager->get('help')) {
                $this->bot->feignTyping($message->getChannel(), "*Error:*\n> " . $e->getMessage());
                return;
            }
        }

        if ($parsedManager->get('help')) {
            // Output usage info
            $this->outputUsage($message, $parsedManager, $this);
        } else {
            // Run the command
            $this->run($message, $parsedManager);
        }
    }

    /**
     * Output the usage statement for this command
     *
     * @param Message $message
     * @param Manager $manager
     */
    protected function outputUsage(Message $message, Manager $manager, SimpleCommand $command)
    {
        $output = [];
        $channel = $message->getChannel();

        $filter = new Filter();
        $filter->setArguments($manager->all());

        $summary = new Summary();

        // Print the description if it's defined.
        if ($command->getDescription()) {
            $output[] = '*' . ucfirst($manager->getCommand()) . '*: ' . $command->getDescription();
            $output[] = '>>>';
        }

        // Output the simple usage statement
        $orderedArguments = array_merge($filter->withPrefix(), $filter->withoutPrefix());
        $output[] = "*Usage:* `{$manager->getCommand()} " . $summary->short($orderedArguments) . '`';

        // Output the detailed arguments
        foreach (['required', 'optional'] as $type) {
            /** @var Argument[] $filteredArguments */
            $filteredArguments = $filter->{$type}();

            if (count($filteredArguments) == 0) {
                continue;
            }

            $output[] = '';
            $output[] = '*' . mb_convert_case($type, MB_CASE_TITLE) . ' Arguments:*';

            foreach ($filteredArguments as $argument) {
                $argumentString = '`' .$summary->argument($argument) . '`';

                if ($description = $argument->description()) {
                    $argumentString .= " _{$description}_";
                }

                $output[] = $argumentString;
            }
        }

        $this->bot->feignTyping($channel, implode(PHP_EOL, $output));
    }

    /**
     * Get the description associated with this command
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}