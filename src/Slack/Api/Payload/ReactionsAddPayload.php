<?php
namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Payload\AbstractPayload;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;

class ReactionsAddPayload extends AbstractPayload
{

    /** @var string */
    protected $name;

    /** @var string */
    protected $channel;

    /** @var string */
    protected $file;

    /** @var string */
    protected $fileComment;

    /** @var string */
    protected $timestamp;

    /**
     * @return string
     */
    public function getMethod()
    {
        return 'reactions.add';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFileComment(): string
    {
        return $this->fileComment;
    }

    /**
     * @param string $fileComment
     */
    public function setFileComment(string $fileComment): void
    {
        $this->fileComment = $fileComment;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     */
    public function setTimestamp(string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * React to a message
     *
     * @param Message $message
     *
     * @return ReactionsAddPayload
     */
    public static function reactTo(Message $message, string $reaction): ReactionsAddPayload
    {
        $self = new self();

        $self->setTimestamp($message->getTimestamp());
        $self->setChannel($message->getChannel());

        $self->setName($reaction);

        return $self;
    }

}