<?php
namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Payload\ChannelsListPayloadResponse;
use PortlandLabs\Slackbot\Slack\Api\Payload\Model\ResponseMetadata;

class ConversationsListPayloadResponse extends ChannelsListPayloadResponse
{

    /** @var ResponseMetadata */
    private $responseMetadata;

    /**
     * Get the next cursor if one is set
     *
     * @return string|null
     */
    public function getNextCursor(): ?string
    {
        return $this->responseMetadata ? $this->responseMetadata->getNextCursor() : null;
    }

}