<?php

namespace PortlandLabs\Slackbot\Command;

use Illuminate\Support\Str;
use PortlandLabs\Slackbot\Bot;

class DefaultProvider implements Provider
{

    /** @var Command[]|string[] */
    protected $commands = [];

    /**
     * Handle adding commands to a bot
     *
     * @param Bot $bot
     * @return mixed
     */
    public function register(Bot $bot)
    {
        $manager = $bot->commands();

        // Add any subclass commands
        foreach ($this->commands as $command) {
            $manager->addCommand($command);
        }

        // Add all the default provided commands
        foreach ($this->findCommands() as $command) {
            $manager->addCommand($command);
        }

        return $manager;
    }


    /**
     * Find commands in the directory we expect to make the most of them
     *
     * @return iterable
     */
    public function findCommands(): iterable
    {
        foreach (scandir(__DIR__, SCANDIR_SORT_NONE) as $file) {
            if ($file === 'Command.php' || !Str::endsWith($file, 'Command.php')) {
                continue;
            }

            // Extract the classname
            $classname = __NAMESPACE__ . '\\' . Str::replaceLast('.php', '', $file);
            if (class_exists($classname) && !interface_exists($classname)) {
                try {
                    $class = new \ReflectionClass($classname);

                    if ($class->isInstantiable()) {
                        yield $classname;
                    }
                } catch (\ReflectionException $e) {
                    // Ignore classes we can't reflect
                }
            }
        }
    }
}