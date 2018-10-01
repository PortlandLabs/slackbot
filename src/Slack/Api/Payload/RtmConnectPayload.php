<?php

namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Payload\AbstractPayload;

class RtmConnectPayload extends AbstractPayload
{

    /**
     * @return string
     */
    public function getMethod()
    {
        return 'rtm.connect';
    }
}
