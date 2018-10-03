<?php

namespace PortlandLabs\Slackbot;

use Carbon\Carbon;
use DateTime;
use PortlandLabs\Slackbot\Command\Manager;
use PortlandLabs\Slackbot\Slack\Api\Client as ApiClient;
use PortlandLabs\Slackbot\Slack\ConnectionManager;
use PortlandLabs\Slackbot\Slack\Rtm\Client as RtmClient;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Middleware\CommandMiddleware;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Deferred;

class Bot
{

    /** @var TimerInterface[] */
    protected $typing = [];

    /** @var ConnectionManager */
    protected $connectionManager;

    /** @var LoggerInterface */
    protected $logger;

    /** @var LoopInterface */
    protected $loop;

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

    protected $commands;

    public function __construct(
        ConnectionManager $connectionManager,
        LoggerInterface $logger,
        LoopInterface $loop,
        Manager $commands)
    {
        $this->connectionManager = $connectionManager;
        $this->logger = $logger;
        $this->loop = $loop;
        $this->commands = $commands;
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
        $promise = $this->connectionManager->connect($this->getLoop(), $this->eventMiddlewares);

        // Record when we connect
        $promise->done(function($result) {
            $this->connecting = false;
            $this->connected = Carbon::now();
        });

        return $promise;
    }

    /**
     * Run the bot
     *
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
     * Get the API client
     *
     * @return ApiClient
     */
    public function api(): ApiClient
    {
        return $this->connectionManager->getApiClient();
    }

    /**
     * Get the RTM client
     *
     * @return RtmClient
     */
    public function rtm(): RtmClient
    {
        return $this->connectionManager->getRtmClient();
    }

    /**
     * Get the command manager
     *
     * @return Manager
     */
    public function commands(): Manager
    {
        return $this->commands;
    }

    /**
     * Get our Logger
     *
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Get the event loop that is powering RTM
     *
     * @return LoopInterface
     */
    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * Get the datetime for when we connected
     *
     * @return DateTime
     */
    public function getConnectedTime(): Carbon
    {
        return $this->connected;
    }

    /**
     * Get the bot ID
     *
     * @return string
     */
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
            $this->rtm()->typing($channel);
            $this->getLoop()->addTimer($duration, function () use ($channel, $message, $deferred) {
                if ($message) {
                    $this->rtm()
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
}
