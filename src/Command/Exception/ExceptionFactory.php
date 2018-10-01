<?php
namespace PortlandLabs\Slackbot\Command\Exception;

class ExceptionFactory
{

    /**
     * Create a new InvalidArgument exception
     *
     * @param $message
     * @return InvalidArgumentException
     */
    public static function invalidArgument($message)
    {
        return new InvalidArgumentException($message);
    }
}