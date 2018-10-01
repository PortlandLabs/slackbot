<?php

namespace PortlandLabs\Slackbot\Command;

use Illuminate\Support\Str;
use InvalidArgumentException;
use PortlandLabs\Slackbot\Command\Argument\Manager;

/**
 * Largely copied / inspired by Laravel's console component: https://github.com/illuminate/console/blob/master/Parser.php
 */
class SignatureParser
{

    /**
     * Parse the given console command definition into an array.
     *
     * @param  string $expression
     * @param Manager $arguments
     * @return Manager
     *
     * @throws \Exception
     */
    public static function parse($expression, Manager $arguments)
    {
        $arguments->setCommand(static::name($expression));

        if (preg_match_all('/\{\s*(.*?)\s*\}/', $expression, $matches)) {
            if (count($matches[1])) {
                $arguments->add(static::parameters($matches[1], $arguments));
            }
        }

        return $arguments;
    }

    /**
     * Extract the name of the command from the expression.
     *
     * @param  string  $expression
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected static function name($expression)
    {
        if (trim($expression) === '') {
            throw new InvalidArgumentException('Console command definition is empty.');
        }

        if (! preg_match('/[^\s]+/', $expression, $matches)) {
            throw new InvalidArgumentException('Unable to determine command name from signature.');
        }

        return $matches[0];
    }

    /**
     * Extract all of the parameters from the tokens.
     *
     * @param  array  $tokens
     * @return array
     */
    protected static function parameters(array $tokens)
    {
        $data = [];
        foreach ($tokens as $token) {
            if (preg_match('/-{2,}(.*)/', $token, $matches)) {
                $argument = static::parseOption($matches[1]);
                $data[$argument['longPrefix']] = $argument;
            } else {
                $argument = static::parseArgument($token);
                $data[$argument['token']] = $argument;
            }
        }

        return $data;
    }

    /**
     * Parse an argument expression.
     *
     *
     * @param  string  $token
     * @return array Options are prefix, longprefix, descriptions, defaultValue, required, noValue
     */
    protected static function parseArgument($token)
    {
        list($token, $description) = static::extractDescription($token);

        $argument = [
            'token' => $token,
            'description' => $description
        ];


        switch (true) {
            case Str::endsWith($token, '?*'):
            case Str::endsWith($token, '?'):
                $argument['token'] = trim($token, '?*');
                $argument['required'] = false;
                break;
            case Str::endsWith($token, '*'):
                $argument['token'] = trim($token, '*');
                $argument['required'] = true;
                break;
            case preg_match('/(.+)\=\*(.+)/', $token, $matches):
            case preg_match('/(.+)\=(.+)/', $token, $matches):
                $argument['token'] = $matches[1];
                $argument['default'] = $matches[2];
                $argument['required'] = false;
                break;
            default:
                $argument['token'] = $token;
                $argument['required'] = true;
                break;
        }

        return $argument;
    }

    /**
     * Parse an option expression.
     *
     * @param  string  $token
     * @return array Options are prefix, longprefix, descriptions, defaultValue, required, noValue
     */
    protected static function parseOption($token)
    {
        list($token, $description) = static::extractDescription($token);

        $matches = preg_split('/\s*\|\s*/', $token, 2);

        if (isset($matches[1])) {
            $shortcut = $matches[0];
            $token = $matches[1];
        } else {
            $shortcut = null;
        }

        $argument = [
            'prefix' => $shortcut,
            'longPrefix' => $token,
            'description' => $description,
            'noValue' => true,
            'required' => false,
        ];

        switch (true) {
            case Str::endsWith($token, '='):
            case Str::endsWith($token, '=*'):
                $argument['noValue'] = false;
                $argument['longPrefix'] = trim($token, '=*');
                break;
            case preg_match('/(.+)\=(.+)/', $token, $matches):
                $argument['noValue'] = false;
                $argument['longPrefix'] = $matches[1];
                $argument['defaultValue'] = $matches[2];
                break;
        }

        return $argument;
    }

    /**
     * Parse the token into its token and description segments.
     *
     * @param  string  $token
     * @return array
     */
    protected static function extractDescription($token)
    {
        $parts = preg_split('/\s+:\s+/', trim($token), 2);

        return count($parts) === 2 ? $parts : [$token, ''];
    }
}