<?php


namespace PortlandLabs\Slackbot\Slack\Rtm;


use CL\Slack\Exception\SlackException;
use PortlandLabs\Slackbot\Slack\Api\Payload\RtmConnectPayload;
use PortlandLabs\Slackbot\Slack\Api\Payload\RtmConnectPayloadResponse;
use PortlandLabs\Slackbot\Slack\Api\Client;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class WebsocketNegotiator
{

    use LoggerAwareTrait;

    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    /**
     * Get a URI for Slack's RTM Api
     *
     * @param Client $client
     * @param int $retries The number of times to retry before giving up. Pass 0 for none, -1 for infinite
     * @param int $maxWait
     *
     * @return RtmConnectPayloadResponse|null
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function resolveUrl(Client $client, $retries = -1, $maxWait = 60): ?RtmConnectPayloadResponse
    {
        $tries = 0;
        $payload = new RtmConnectPayload();
        $wait = 5;
        $result = null;

        try {
            retry:
            $result = $client->send($payload);
        } catch (SlackException $e) {
            $this->logger->debug('Error connecting to RTM: ' . $e->getMessage());
            // Ignore slack errors, retries should handle this
        }

        // Handle retrying the connection. This prevents excessive retries or our process manager dieing on us
        if (!$result || !$result instanceof RtmConnectPayloadResponse) {
            if ($retries > 0 || $retries < 0) {
                $retries--;
                $tries++;

                $this->logger->debug('Error connecting to RTM, waiting ' . $wait . ' seconds then retrying...');

                // Sleep for the intended wait time
                sleep($wait);

                // Double the wait time, or hit the max wait time
                $wait = min($wait * 2, $maxWait);
                goto retry;
            }
        }

        if ($result instanceof RtmConnectPayloadResponse) {
            return $result;
        }
    }
}