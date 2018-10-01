<?php

namespace PortlandLabs\Slackbot\Slack\Api;

use CL\Slack\Exception\SlackException;
use CL\Slack\Payload\FilesUploadPayload;
use CL\Slack\Payload\PayloadInterface;
use CL\Slack\Payload\PayloadResponseInterface;
use CL\Slack\Serializer\PayloadResponseSerializer;
use CL\Slack\Serializer\PayloadSerializer;
use CL\Slack\Transport\ApiClientInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use PortlandLabs\Slackbot\Slack\Api\Payload\Builder;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * This class is largely written by Cas Leentfaar
 *
 * @author Cas Leentfaar <info@casleentfaar.com>
 */
class Client implements ApiClientInterface
{

    /**
     * The (base) URL used for all communication with the Slack API.
     */
    const API_BASE_URL = 'https://slack.com/api/';

    /**
     * Event triggered just before it's sent to the Slack API
     * Any listeners are passed the request data (array) as the first argument.
     */
    const EVENT_REQUEST = 'EVENT_REQUEST';

    /**
     * Event triggered just before it's sent to the Slack API
     * Any listeners are passed the response data (array) as the first argument.
     */
    const EVENT_RESPONSE = 'EVENT_RESPONSE';


    use LoggerAwareTrait;

    /**
     * @var string|null
     */
    protected $token;

    /**
     * @var PayloadSerializer
     */
    protected $payloadSerializer;

    /**
     * @var PayloadResponseSerializer
     */
    protected $payloadResponseSerializer;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $username;

    /**
     * @param string|null $token
     * @param ClientInterface|null $client
     * @param PayloadSerializer $serializer
     * @param PayloadResponseSerializer $responseSerializer
     * @param LoggerInterface $logger
     * @param Builder $builder
     */
    public function __construct(
        $token,
        ClientInterface $client,
        PayloadSerializer $serializer,
        PayloadResponseSerializer $responseSerializer,
        LoggerInterface $logger,
        Builder $builder
    )
    {
        $this->token = $token;
        $this->payloadSerializer = $serializer;
        $this->payloadResponseSerializer = $responseSerializer;
        $this->client = $client;
        $this->setLogger($logger);
        $this->builder = $builder;
    }

    /**
     * Send a payload using the client
     *
     * @param PayloadInterface $payload
     * @param null $token
     * @return PayloadResponseInterface
     * @throws SlackException
     * @throws GuzzleException
     */
    public function send(PayloadInterface $payload, $token = null)
    {
        try {
            if ($token === null && $this->token === null) {
                throw new \InvalidArgumentException('You must supply a token to send a payload, since you did not provide one during construction');
            }

            $content = $file = '';
            if ($payload instanceof FilesUploadPayload) {
                $content = $payload->getContent();
                $file = $payload->getFile();
                $payload->setContent('');
                $payload->setFile('');
            }

            $serializedPayload = $this->payloadSerializer->serialize($payload);

            if ($payload instanceof FilesUploadPayload) {
                $payload->setContent($content);
                $payload->setFile($file);
                $serializedPayload['content'] = $content;
                $serializedPayload['file'] = $file;
                $content = $file = null;
            }

            $this->logger->debug('[API -> ] ' . $payload->getMethod() . ': ' . json_encode($serializedPayload));

            $responseData = $this->doSend($payload->getMethod(), $serializedPayload, $token);
            $this->logger->debug('[API <- ] ' . $payload->getMethod() . ': ' . json_encode($responseData));

            return $this->payloadResponseSerializer->deserialize($responseData, $payload->getResponseClass());
        } catch (\Exception $e) {
            throw new SlackException(sprintf('Failed to send payload: %s', $e->getMessage()), null, $e);
        }
    }

    /**
     * Handle sending data to the API
     *
     * @param string $method
     * @param array $data
     * @param null $token
     * @return array|mixed
     * @throws SlackException
     * @throws GuzzleException
     */
    private function doSend($method, array $data, $token = null)
    {
        try {
            $data['token'] = $token ?: $this->token;

            $request = $this->createRequest($method, $data);

            /** @var ResponseInterface $response */
            $response = $this->client->send($request);
        } catch (\Exception $e) {
            throw new SlackException('Failed to send data to the Slack API', null, $e);
        }

        try {
            $responseData = json_decode($response->getBody()->getContents(), true);
            if (!is_array($responseData)) {
                throw new \Exception(sprintf(
                    'Expected JSON-decoded response data to be of type "array", got "%s"',
                    gettype($responseData)
                ));
            }

            return $responseData;
        } catch (\Exception $e) {
            throw new SlackException('Failed to process response from the Slack API', null, $e);
        }
    }

    /**
     * @param string $method
     * @param array $payload
     *
     * @return RequestInterface
     */
    private function createRequest($method, array $payload)
    {
        $request = new Request(
            'POST',
            self::API_BASE_URL . $method,
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($payload)
        );

        return $request;
    }

    /**
     * Set the API username to use
     *
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * Get the Payload builder object
     *
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder->prepare($this->username);
    }

}