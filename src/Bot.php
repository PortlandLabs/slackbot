<?php

namespace PortlandLabs\Slackbot;

use Carbon\Carbon;
use DateTime;
use PortlandLabs\Slackbot\Slack\Api\Payload\RtmConnectPayloadResponse;
use PortlandLabs\Slackbot\Slack\Api\Client as ApiClient;
use PortlandLabs\Slackbot\Slack\Rtm\Client as RtmClient;
use PortlandLabs\Slackbot\Slack\Rtm\Event;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Middleware\CommandMiddleware;
use PortlandLabs\Slackbot\Slack\Rtm\WebsocketNegotiator;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Deferred;

class Bot
{

    /** @var TimerInterface[] */
    protected $typing = [];

    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * @var RtmClient
     */
    protected $rtmClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var WebsocketNegotiator
     */
    protected $negotiator;

    /** @var Carbon */
    protected $connected;

    /** @var bool */
    protected $connecting;

    /** @var string */
    protected $id;

    /**
     * @var string[]
     */
    protected $eventMiddlewares = [
        CommandMiddleware::class
    ];

    public function __construct(ApiClient $apiClient, RtmClient $rtmClient, LoggerInterface $logger, LoopInterface $loop, WebsocketNegotiator $negotiator)
    {
        $this->apiClient = $apiClient;
        $this->rtmClient = $rtmClient;
        $this->logger = $logger;
        $this->loop = $loop;
        $this->negotiator = $negotiator;
        $this->id = bin2hex(random_bytes(4));
    }

    /**
     * Connect to slack
     *
     * @return \React\Promise\Promise
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function connect()
    {
        $this->connecting = true;
        $payload = $this->negotiator->resolveUrl($this->api(), 10);
        $promise = $this->rtmClient->listen($this->loop, $payload, $this->eventMiddlewares, function(Event $event) {
            $this->logger()->debug('[BOT.RCV] Received Event: ' . get_class($event));
        });

        // Record when we connected
        $promise->done(function($result) {
            /** @var RtmConnectPayloadResponse $payload */
            [$connection, $payload] = $result;

            $this->api()->setUsername($payload->getUserName());
            $this->connecting = false;
            $this->connected = Carbon::now();

            // Start sending Pings
            $this->startPingLoop();
        });

        return $promise;
    }

    /**
     * Run the bot
     * This method must be called in order to start running the bot
     */
    public function run()
    {
        if (!$this->connected && !$this->connecting) {
            $this->connect();
        }

        $this->loop->run();
    }

    /**
     * Stop a running bot
     */
    public function stop()
    {
        $this->rtm()->disconnect();
        $this->loop->stop();
    }

    /**
     * @return ApiClient
     */
    public function api(): ApiClient
    {
        return $this->apiClient;
    }

    /**
     * @return RtmClient
     */
    public function rtm(): RtmClient
    {
        return $this->rtmClient;
    }

    /**
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return LoopInterface
     */
    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * @return DateTime
     */
    public function getConnectedTime(): DateTime
    {
        return $this->connected;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Pretend to type
     *
     * @param string $channel
     * @param string $message
     * @param float $duration
     *
     * @return \React\Promise\Promise|\React\Promise\Promise
     */
    public function feignTyping(string $channel, string $message = null, float $duration = .1)
    {
        $deferred = new Deferred();
        $duration = max(0, $duration);

        $this->logger->info('[<bold>BOT.TYP</bold>] Beginning to type');

        if ($duration) {
            $this->rtmClient->typing($channel);
            $this->getLoop()->addTimer($duration, function () use ($channel, $message, $deferred) {
                if ($message) {
                    $this->rtmClient
                        ->sendMessage($message, $channel)
                        ->done(function (array $result) use ($deferred) {
                            $this->logger->info('[<bold>BOT.TYP</bold>] Done Typing');
                            $deferred->resolve($result);
                        });
                } else {
                    $deferred->resolve();
                }
            });
        }

        return $deferred->promise();
    }

    /**
     * Get a string representing how long we've been up
     *
     * @param DateTime|null $now
     *
     * @return string
     */
    public function getUptime(DateTime $now = null): string
    {
        if (!$now) {
            $now = Carbon::now();
        }

        $diffString = $this->connected->diffForHumans($now, true, false, 6);

        // Return the time string with "and" between the last two segments
        return preg_replace('/(\d+ \D+?) (\d+ \D+?)$/', '$1 and $2', $diffString);

    }

    /**
     * Start sending pings over RTM to keep us alive
     */
    protected function startPingLoop()
    {
        // Track how many times pings fail
        $fails = 0;

        $this->getLoop()->addPeriodicTimer(10, function() use (&$fails) {
            $this->rtmClient->sendPing()->otherwise(function() use (&$fails) {
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

}
