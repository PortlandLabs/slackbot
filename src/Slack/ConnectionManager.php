<?php
namespace PortlandLabs\Slackbot\Slack;

use PortlandLabs\Slackbot\Slack\Api\Payload\RtmConnectPayloadResponse;
use PortlandLabs\Slackbot\Slack\Rtm\WebsocketNegotiator;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

class ConnectionManager
{

    /** @var Api\Client */
    protected $apiClient;

    /** @var Rtm\Client */
    protected $rtmClient;

    /** @var WebsocketNegotiator */
    protected $negotiator;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Api\Client $apiClient, Rtm\Client $rtmClient, WebsocketNegotiator $negotiator, LoggerInterface $logger)
    {
        $this->apiClient = $apiClient;
        $this->rtmClient = $rtmClient;
        $this->negotiator = $negotiator;
        $this->logger = $logger;
    }

    /**
     * Connect to slack
     *
     * @return \React\Promise\Promise
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function connect(LoopInterface $loop, array $middleware)
    {
        $this->connecting = true;
        $payload = $this->negotiator->resolveUrl($this->apiClient, 10);

        if (!$payload) {
            $this->logger->critical('[CON.ERR] Failed to negotiate websocket URL.');
            throw new \RuntimeException('Failed to negotiate websocket URL.');
        }

        // Start listening
        $promise = $this->rtmClient->listen($loop, $payload, $middleware, function(){});

        // Handle connecting
        $promise->done(function($result) use ($loop) {
            /** @var RtmConnectPayloadResponse $payload */
            [$connection, $payload] = $result;

            $this->apiClient->setUsername($payload->getUserName());

            // Start sending Pings
            $this->startPingLoop($loop);
        });

        return $promise;
    }

    /**
     * Start sending pings over RTM to keep us alive
     *
     * @param LoopInterface $loop
     */
    protected function startPingLoop(LoopInterface $loop)
    {
        // Track how many times pings fail
        $fails = 0;

        $loop->addPeriodicTimer(10, function () use (&$fails) {
            $this->rtmClient->sendPing()->otherwise(function () use (&$fails) {
                $fails++;

                if ($fails > 3) {
                    $this->handleInterrupt();
                    $fails = 0;
                }
            });
        });
    }

    /**
     * Handle the RTM session getting interrupted
     */
    protected function handleInterrupt()
    {
        $this->logger->critical('-- Disconnected from RTM, Ping timeout --');
        exit;
    }

    /**
     * @return Api\Client
     */
    public function getApiClient(): Api\Client
    {
        return $this->apiClient;
    }

    /**
     * @return Rtm\Client
     */
    public function getRtmClient(): Rtm\Client
    {
        return $this->rtmClient;
    }

}