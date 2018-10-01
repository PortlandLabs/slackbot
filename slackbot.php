<?php

use PortlandLabs\Slackbot\Bot;

require __DIR__ . '/bootstrap/env.php';

// Get a container implementation
$container = \PortlandLabs\Slackbot\ContainerFactory::illuminate();

/** @var Bot $bot */
$bot = $container->get(Bot::class);

// Connect and run the bot
$bot->run();