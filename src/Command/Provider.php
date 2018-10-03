<?php
namespace PortlandLabs\Slackbot\Command;

use PortlandLabs\Slackbot\Bot;

interface Provider
{

    /**
     * Handle adding commands to a bot
     *
     * @param Bot $bot
     * @return mixed
     */
    public function register(Bot $bot);

}