<?php

namespace PortlandLabs\Slackbot\Command\Argument;

use League\CLImate\Argument\Manager as ArgumentManager;

class Manager extends ArgumentManager
{

    /** @var string */
    protected $command;

    /** @var string The Role required */
    protected $role;

    /**
     * Manage parsing the incoming $argv
     *
     * @param string[] $argv
     * @throws \Exception
     */
    public function parse(array $argv = null)
    {
        $this->command = $this->parser->command($argv);
        parent::parse($argv);
    }

    /**
     * Get the command these arguments are associated with
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set the command that is associated with these arguments
     *
     * @param string $command
     */
    public function setCommand(string $command)
    {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * Manage cloning arguments on clone
     */
    public function __clone()
    {
        $this->arguments = array_map(function($item) {
            return clone $item;
        }, $this->arguments);
    }

}
