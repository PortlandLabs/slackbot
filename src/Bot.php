<?php

namespace PortlandLabs\Slackbot;

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

    /** @var \DateTime */
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
            $this->connected = new \DateTime();
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
     * @return \DateTime
     */
    public function getConnectedTime(): \DateTime
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
     * @return string
     */
    public function getUptime(): string
    {
        $now = new \DateTime();
        $start = $this->getConnectedTime();

        $diff = $start->diff($now);
        $details = [];

        if ($diff->y) {
            $details[] = "$diff->y year" . ($diff->y > 1 ? 's' : '');
        }

        if ($diff->m) {
            $details[] = "$diff->m month" . ($diff->m > 1 ? 's' : '');
        }

        if ($diff->d) {
            $details[] = "$diff->d day" . ($diff->d > 1 ? 's' : '');
        }

        if ($diff->h) {
            $details[] = "$diff->h hour" . ($diff->h > 1 ? 's' : '');
        }

        if ($diff->i) {
            $details[] = "$diff->i minute" . ($diff->i > 1 ? 's' : '');
        }

        if ($diff->s) {
            $details[] = "$diff->s second" . ($diff->s > 1 ? 's' : '');
        }

        $last = null;
        if (count($details) > 1) {
            $last = array_pop($details);
        }

        return implode(' ', $details) . ($last ? " and $last" : '');
    }

}
