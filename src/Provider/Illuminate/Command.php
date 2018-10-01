<?php
namespace PortlandLabs\Slackbot\Provider\Illuminate;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use PortlandLabs\Slackbot\Command\Manager;

class Command implements Provider
{

    protected $commands = [
    ];

    /**
     * Register this provider
     *
     * @param Container $container
     *
     * @return void
     */
    public function register(Container $container)
    {
        $container->extend(Manager::class, function(Manager $manager) use ($container) {
            foreach ($this->commands as $command) {
                $manager->addCommand($container->get($command));
            }

            foreach ($this->findCommands($container) as $command) {
                $manager->addCommand($command);
            }

            return $manager;
        });
    }

    /**
     * Find commands in the directory we expect to make the most of them
     *
     * @return iterable
     */
    public function findCommands(Container $container): iterable
    {
        $dir = __DIR__ . '/../../Command';
        $namespace = '\PortlandLabs\Slackbot\Command\\';

        foreach (scandir($dir) as $file) {
            if (!Str::endsWith($file, 'Command.php')) {
                continue;
            }

            $classname = $namespace . Str::replaceLast('.php', '', $file);
            if (class_exists($classname)) {
                try {
                    yield $container->get($classname);
                } catch (BindingResolutionException $exception) {
                    // Ignore ones we can't build
                    continue;
                }
            }
        }
    }
}