<?php

namespace PortlandLabs\Slackbot\Slack\Rtm;

use PortlandLabs\Slackbot\Slack\Api\Payload\RtmConnectPayload;
use PortlandLabs\Slackbot\Slack\Api\Payload\RtmConnectPayloadResponse;
use PortlandLabs\Slackbot\Slack\Api\Client as ApiClient;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Factory;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Handler;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Middleware;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Message;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

use function Ratchet\Client\connect as websocketConnect;

class Client
{

    use LoggerAwareTrait;

    /** @var ContainerInterface  */
    protected $container;

    /** @var ApiClient */
    protected $client;

    /** @var Factory */
    protected $eventFactory;

    /** @var WebSocket */
    protected $socket;

    /** @var callable */
    protected $listener;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $userId;

    /** @var Deferred The promise associated with this client */
    protected $deferred;

    /** @var int Tracks the current message identifier for the RTM api */
    protected $messageId = 1;

    /** @var bool */
    protected $connected = false;

    public function __construct(ContainerInterface $container, Factory $eventFactory, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->eventFactory = $eventFactory;
        $this->setLogger($logger);
    }

    /**
     * Handle shutting down
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Disconnect from RTM stream
     *
     * @return bool
     */
    public function disconnect(): bool
    {
        if ($this->socket) {
            $this->logger->info('[<bold>RTM.DSC]</bold>] Disconnecting.');

            // Stop listening
            $this->socket->removeListener('message', $this->listener);

            // Close the websocket
            $this->socket->close();
            $this->socket = null;

            // Unset stuff
            $this->userId = null;
            $this->userName = null;
            $this->listener = null;
            $this->connected = false;

            return true;
        }

        return false;
    }

    /**
     * Begin listening to events
     *
     * @param LoopInterface $loop
     * @param RtmConnectPayloadResponse $rtmPayload
     * @param array $middleware
     * @param callable $callback
     *
     * @return Promise
     */
    public function listen(LoopInterface $loop, RtmConnectPayloadResponse $rtmPayload, array $middleware, callable $callback): Promise
    {
        if ($this->socket) {
            throw new \RuntimeException('This client is already listening.');
        }

        // Build a stack of middleware with the passed callback at the center
        $middleware = array_reverse($middleware);

        /** @var Handler $stack */
        $stack = array_reduce($middleware, [$this, 'buildDelegateHandler'], new Handler\Dispatcher($callback));

        // Get our promise and add a listener when it resolves
        $promise = $this->getPromise();
        $promise->done(function() use ($stack) {
            $this->logger->debug('-- Starting to listen... --');

            $this->listener = function (Message $message) use ($stack) {
                if (!$this->connected) {
                    // After we disconnect stop listening.
                    return false;
                }

                $this->logger->debug('[<bold>RTM <- </bold>] ' . $message);

                // Decode the payload
                $payload = $message->getPayload();
                $data = json_decode($payload, true);

                // If we can build an event from it, send it through the middleware
                if ($event = $this->eventFactory->buildEvent($data)) {
                    $this->logger->debug('[<bold>RTM.EVT</bold>] Sending through middleware...');
                    $stack->handle($event);
                }
            };

            // Listen to messages
            $this->socket->on('message', $this->listener);
        });

        // Try to connect
        $this->connect($loop, $rtmPayload);

        return $promise;
    }

    /**
     * Build a delegate handler given a middleware to delegate to, and a handler to pass in
     *
     * @param Handler $handler
     * @param Middleware|string $middleware
     * @return Handler
     */
    protected function buildDelegateHandler(Handler $handler, $middleware): Handler
    {
        if (is_string($middleware)) {
            $middleware = $this->container->get($middleware);
        }

        return new Handler\Delegate($middleware, $handler);
    }

    /**
     * Get the promise associated with this client
     * This promise resolves when the connection is established
     *
     * @return Promise|\React\Promise\PromiseInterface
     */
    public function getPromise(): PromiseInterface
    {
        return $this->getDeferred()->promise();
    }

    /**
     * Get or create a new Deferred promise for our
     *
     * @return Deferred
     */
    protected function getDeferred(): Deferred
    {
        if (!$this->deferred) {
            $this->deferred = new Deferred();
        }

        return $this->deferred;
    }

    /**
     * Show the bot as typing
     * @param string $channel
     */
    public function typing(string $channel)
    {
        $this->send('typing', ['channel' => $channel]);
    }

    /**
     * Send some data into the socket
     *
     * @param string $type
     * @param array $data
     */
    public function send(string $type, array $data = [])
    {
        $data['type'] = $type;
        $data['id'] = $this->messageId++;

        $serialized = json_encode($data);
        $this->logger->debug('[<bold>RTM -> </bold>] ' . $serialized);
        $this->socket->send($serialized);
    }

    /**
     * Send a message
     *
     * @param string $message
     * @param array $data
     * @return Promise
     */
    public function sendMessage(string $message, string $channel, array $data = []): Promise
    {
        $deferred = new Deferred();

        $data['text'] = $message;
        $data['channel'] = $channel;
        $currentId = $this->messageId;
        $listener = null;

        // Listen for a reply
        $listener = function (Message $message) use ($deferred, $currentId, &$listener) {
            $payload = json_decode($message->getPayload(), true);
            $replyTo = $payload['reply_to'] ?? 0;

            // If this payload is in reply to our message
            if ($replyTo === $currentId) {
                $deferred->resolve($payload);
            }

            if ($this->socket) {
                $this->socket->removeListener('message', $listener);
            }
        };

        $this->socket->on('message', $listener);

        // Send our data
        $this->send('message', $data);
        return $deferred->promise();
    }

    /**
     * Connect to our RTM websocket
     *
     * @param LoopInterface $loop
     * @param RtmConnectPayloadResponse $rtmPayload
     */
    protected function connect(LoopInterface $loop, RtmConnectPayloadResponse $rtmPayload)
    {
        $deferred = $this->deferred;

        // Manage connecting
        $connection = websocketConnect($rtmPayload->getUrl(), [], [], $loop);
        $connection->then(
            function (WebSocket $connection) use ($deferred, $rtmPayload) {
                $this->connected($connection, $rtmPayload);
                $deferred->resolve([$connection, $rtmPayload]);

                return $connection;
            },

            // Failure
            function($e) use ($deferred) {
                $this->logger->error('[<bold>RTM.ERR</bold>] Websocket rejected: ' . $e);
                $deferred->reject($e);
            }
        );
    }

    /**
     * Manage what happens post connect
     *
     * @param WebSocket $socket
     * @param RtmConnectPayloadResponse $rtmPayload
     */
    protected function connected(WebSocket $socket, RtmConnectPayloadResponse $rtmPayload)
    {
        $this->logger->info('<bold>-- Connected! --</bold>');
        $this->socket = $socket;
        $this->userName = $rtmPayload->getUserName();
        $this->userId = $rtmPayload->getUserId();
        $this->url = $rtmPayload->getUrl();
        $this->connected = true;
    }

    /**
     * Get the active user name
     *
     * @return string|null
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * Get the active user id
     *
     * @return string|null
     */
    public function getUserId(): ?string
    {
        return $this->userId;
    }

}