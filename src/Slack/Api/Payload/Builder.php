<?php
namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Payload\ChatPostMessagePayload;
use CL\Slack\Payload\ChatPostMessagePayloadResponse;
use CL\Slack\Payload\PayloadInterface;
use CL\Slack\Payload\PayloadResponseInterface;
use PortlandLabs\Slackbot\Slack\Api\Client;

class Builder
{

    /** @var ChatPostMessagePayload|ChatUpdatePayload */
    protected $payload;

    /** @var ChatPostMessagePayload */
    protected $lastPayload;

    /** @var ChatPostMessagePayloadResponse */
    protected $lastResponse;

    /** @var string */
    protected $username;

    /**
     * Prepare to start building
     *
     * @param string $username
     *
     * @return Builder
     * @internal
     */
    public function prepare(string $username): Builder
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Create a new ChatPostMessagePayload builder flow
     *
     * @param string $text
     *
     * @return Builder
     */
    public function send($text = ''): Builder
    {
        $this->payload = new ChatPostMessagePayload();
        $this->payload->setText($text);
        $this->payload->setUsername($this->username);
        $this->lastResponse = null;
        $this->lastPayload = null;

        return $this;
    }

    /**
     * Create a new ChatUpdatePayload builder flow
     *
     * @return Builder
     */
    public function update(): Builder
    {
        if (!$this->lastPayload || !$this->lastResponse) {
            throw new \RuntimeException('No chat post to update.');
        }

        $response = $this->lastResponse;
        $message = $this->lastPayload;

        $payload = new ChatUpdatePayload();
        $payload->setSlackTimestamp($response->getSlackTimestamp());
        $payload->setChannelId($response->getChannelId());
        $payload->setText($message->getText());
        $payload->setLinkNames($message->getLinkNames());
        $payload->setParse($message->getParse());

        // Set the new payload on the new builder
        $this->payload = $payload;

        return $this;
    }

    public function updateRtmMessage(array $responseData): Builder
    {
        $payload = new ChatUpdatePayload();
        $payload->setSlackTimestamp(array_get($responseData, 'timestamp'));
        $payload->setChannelId(array_get($responseData, 'channel'));
        $payload->setText(array_get($responseData, 'text'));

        // Set the new payload on the new builder
        $this->payload = $payload;

        return $this;
    }

    /**
     * Set the channel to send to
     * Note: Only works when sending new messages, not when updating
     *
     * @param $channel
     *
     * @return Builder
     */
    public function to($channel): Builder
    {
        if (!$this->payload instanceof ChatPostMessagePayload) {
            throw new \RuntimeException('You can\'t update the channel, use ->send to create a new message instead first');
        }

        $this->payload->setChannel($channel);
        return $this;
    }

    /**
     * Set the icon on the active builder
     * Note: Only works when sending new messages, not when updating
     *
     * @param string $icon
     * @return Builder
     */
    public function withIcon(string $icon): Builder
    {
        if (!$this->payload instanceof ChatPostMessagePayload) {
            throw new \RuntimeException('You can\'t update the icon');
        }

        $this->payload->setIconEmoji($icon);
        return $this;
    }

    /**
     * Set the icon on the active builder
     * Note: Only works when sending new messages, not when updating
     *
     * @param string $iconUrl
     * @return Builder
     */
    public function withIconUrl(string $iconUrl): Builder
    {
        if (!$this->payload instanceof ChatPostMessagePayload) {
            throw new \RuntimeException('You can\'t update the icon');
        }

        $this->payload->setIconUrl($iconUrl);
        return $this;
    }

    /**
     * Set the text on the active payload
     *
     * @param string $text
     * @return Builder
     */
    public function withText(string $text): Builder
    {
        $this->payload->setText($text);
        return $this;
    }

    /**
     * Add an attachment to the active payload
     *
     * @param AttachmentPayload $attachment
     * @return Builder
     */
    public function withAttachment(AttachmentPayload $attachment): Builder
    {
        $this->payload->addAttachment($attachment);
        return $this;
    }

    /**
     * Execute the active payload on the client
     *
     * @param Client $client
     *
     * @return PayloadResponseInterface
     *
     * @throws \CL\Slack\Exception\SlackException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(Client $client): PayloadResponseInterface
    {
        $result = $client->send($this->payload);

        if ($result instanceof ChatPostMessagePayloadResponse) {
            $this->lastPayload = clone $this->payload;
            $this->lastResponse = $result;
        }

        return $result;
    }

    /**
     * Get the active payload
     *
     * @return ChatPostMessagePayload|ChatUpdatePayload
     */
    public function payload(): PayloadInterface
    {
        return $this->payload;
    }
}