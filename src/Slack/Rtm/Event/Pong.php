<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event;

use PortlandLabs\Slackbot\Slack\Rtm\Event;

/**
 * A reply to a "ping" message
 */
final class Pong implements Event
{

    /** @var array */
    protected $data;

    /** @var int */
    protected $replyTo;

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return Pong
     */
    public function setData(array $data): Pong
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return int
     */
    public function getReplyTo(): int
    {
        return $this->replyTo;
    }

    /**
     * @param int $replyTo
     * @return Pong
     */
    public function setReplyTo(int $replyTo): Pong
    {
        $this->replyTo = $replyTo;
        return $this;
    }

}