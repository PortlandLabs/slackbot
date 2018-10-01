<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

trait MessageTrait
{

    /** @var string */
    private $subtype;

    /** @var string */
    private $timestamp;

    /** @var string */
    private $user;

    /** @var string */
    private $channel;

    /** @var string */
    private $message;

    /**
     * Get the subtype of the message
     *
     * @see https://api.slack.com/events/message for subtypes
     *
     * @return null|string
     */
    public function getSubtype(): ?string
    {
        return $this->subtype;
    }

    /**
     * Set the message subtype
     *
     * @param string $subtype
     * @return null|self
     */
    public function setSubtype(string $subtype): ?Message
    {
        $this->subtype = $subtype;
        return $this->forceInterface();
    }

    /**
     * Get the timestamp associated with the message
     *
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * Set the message timestamp
     *
     * @param string $timestamp
     * @return null|self
     */
    public function setTimestamp(string $timestamp): ?Message
    {
        $this->timestamp = $timestamp;
        return $this->forceInterface();
    }

    /**
     * Get the user associated with the message
     *
     * @return null|string
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): ?Message
    {
        $this->user = $user;
        return $this->forceInterface();
    }

    /**
     * Get the channel associated with the message
     *
     * @return null|string
     */
    public function getChannel(): ?string
    {
        return $this->channel;
    }

    /**
     * Set the channel
     *
     * @param string $channel
     * @return null|self
     */
    public function setChannel(string $channel): ?Message
    {
        $this->channel = $channel;
        return $this->forceInterface();
    }

    /**
     * Get the message
     *
     * @return null|string
     */
    public function getText(): ?string
    {
        return $this->message;
    }

    /**
     * Set the message
     *
     * @param string $message
     * @return null|self
     */
    public function setText(string $message): ?Message
    {
        $this->message = $message;
        return $this->forceInterface();
    }

    /**
     * Ensure we return a Message interface implementation, otherwise return null
     *
     * @return null|self
     */
    private function forceInterface(): ?Message
    {
        return $this instanceof Message ? $this : null;
    }
}