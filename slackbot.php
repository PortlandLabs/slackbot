<?php

use PortlandLabs\Slackbot\Bot;
use PortlandLabs\Slackbot\ContainerFactory;
use PortlandLabs\Slackbot\Command;

require __DIR__ . '/bootstrap/env.php';

// Get a container implementation
$container = ContainerFactory::illuminate();

/** @var Bot $bot */
$bot = $container->get(Bot::class);

// Add default commands to the bot
/** @var Command\Provider $provider */
$provider = $container->get(Command\DefaultProvider::class);
$provider->register($bot);

// Connect and run the bot
$bot->run();