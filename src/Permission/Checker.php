<?php
namespace PortlandLabs\Slackbot\Permission;

use Buttress\Collection\GeneratorCollection;
use CL\Slack\Exception\SlackException;
use CL\Slack\Model\Channel;
use CL\Slack\Payload\ChannelsInfoPayload;
use CL\Slack\Payload\ChannelsInfoPayloadResponse;
use CL\Slack\Payload\ChannelsListPayload;
use CL\Slack\Payload\ChannelsListPayloadResponse;
use GuzzleHttp\Exception\GuzzleException;
use PortlandLabs\Slackbot\Slack\Api\Client;
use PortlandLabs\Slackbot\Slack\Api\Payload\ChatUpdatePayload;
use PortlandLabs\Slackbot\Slack\Api\Payload\ConversationsListPayload;
use PortlandLabs\Slackbot\Slack\Api\Payload\ConversationsListPayloadResponse;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class Checker
{
    use LoggerAwareTrait;

    /** @var Client */
    protected $client;

    /** @var Admin */
    protected $adminRole;

    /** @var User */
    protected $userRole;

    /** @var Bot */
    protected $botRole;

    /** @var string */
    protected $adminChannelId;

    public function __construct(Client $client, LoggerInterface $logger, User $userRole, Admin $adminRole, Bot $botRole)
    {
        $this->client = $client;
        $this->setLogger($logger);
        $this->userRole = $userRole;
        $this->adminRole = $adminRole;
        $this->botRole = $botRole;
    }

    /**
     * Get the role of a person using the bot
     *
     * @param Message $message
     *
     * @return Role
     */
    public function getRole(Message $message): Role
    {
        if ($message->getSubtype() === 'bot_message') {
            return $this->botRole;
        }

        return $message->getChannel() === $this->getAdminChannel() ? $this->adminRole : $this->userRole;
    }

    /**
     * Determine the admin channel ID
     *
     * @return string
     */
    private function getAdminChannel(): ?string
    {
        if ($this->adminChannelId === null) {
            $adminChannel = null;

            if ($channel = getenv('ADMIN_CHANNEL')) {
                $adminChannel = $this->resolveAdminChannel($channel);
            }

            $this->adminChannelId = $adminChannel ?: false;
        }

        return $this->adminChannelId ?: null;
    }

    /**
     * Find the channel that matches our admin channel name
     *
     * @param string $name
     * @return string The admin channel id or null if not found
     */
    protected function resolveAdminChannel(string $name): ?string
    {
        foreach ($this->allChannels() as $channel) {
            if ($channel->getName() === $name) {
                return $channel->getId();
            }
        }

        return null;
    }

    /**
     * Get all channels
     *
     * @return Channel[]
     */
    private function allChannels(): iterable
    {
        $payload = new ConversationsListPayload();
        $payload->setExcludePrivate(false);
        $payload->setExcludePublic(true);
        $payload->setExcludeArchived(true);
        $response = null;
        $nextCursor = null;

        do {
            if ($nextCursor) {
                $payload->setCursor($nextCursor);
            }

            try {
                /** @var ConversationsListPayloadResponse $response */
                $response = $this->client->send($payload);
            } catch (SlackException | GuzzleException $e) {
                $this->logger->notice('Unable to request channel list: ' . $e->getMessage());
            }

            if ($response) {
                $channels = (array) $response->getChannels();
                foreach ($channels as $channel) {
                    yield $channel;
                }

                $nextCursor = $response->getNextCursor();
            }
        } while ($nextCursor);
    }

}