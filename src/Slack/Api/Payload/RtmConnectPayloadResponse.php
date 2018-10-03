<?php

namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Payload\AbstractPayloadResponse;
use JMS\Serializer\Annotation\Type;

class RtmConnectPayloadResponse extends AbstractPayloadResponse
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string[]
     */
    private $self;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function getUserId()
    {
        return $this->self[0];
    }

    public function getUserName()
    {
        return $this->self[1];
    }

}
