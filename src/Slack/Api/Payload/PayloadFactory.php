<?php

namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Payload\ChatPostMessagePayload;

class PayloadFactory
{

    /**
     * Create an error message Payload
     * @param string $message
     * @param string $channel
     * @return \CL\Slack\Payload\ChatPostMessagePayload
     */
    public function test($message, $channel, $prefix = 'Testing: ')
    {
        $payload = new ChatPostMessagePayload();
        $payload->setText(sprintf('%s %s', $prefix, $message));
        $payload->setChannel($channel);
        $payload->setIconEmoji(':grey_question:');

        return $payload;
    }

    /**
     * Create an error message Payload
     * @param string $message
     * @param string $channel
     * @return \CL\Slack\Payload\ChatPostMessagePayload
     */
    public function error($message, $channel, $prefix = 'Ran into trouble: ')
    {
        $payload = new ChatPostMessagePayload();
        $payload->setText(sprintf('%s %s', $prefix, $message));
        $payload->setChannel($channel);
        $payload->setIconEmoji(':x:');

        return $payload;
    }

}
