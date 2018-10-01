<?php
namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Payload\ChannelsListPayload;

class ConversationsListPayload extends ChannelsListPayload
{

    /** @var string */
    protected $types = 'public_channel';

    /** @var string */
    protected $cursor;

    public function getMethod()
    {
        return 'conversations.list';
    }

    /**
     * Set whether we exclude public channels
     *
     * @param bool $exclude
     */
    public function setExcludePublic(bool $exclude)
    {
        $this->toggleType('public_channel', $exclude);
    }

    /**
     * Set whether we exclude public channels
     * @param bool $exclude
     */
    public function setExcludePrivate(bool $exclude)
    {
        $this->toggleType('private_channel', $exclude);
    }

    /**
     * Toggle types from our $types array
     *
     * @param string $type
     * @param bool $exclude
     */
    public function toggleType(string $type, bool $exclude)
    {
        $typeArray = explode(',', $this->types);
        $key = array_search($type, $typeArray);

        if ($exclude && $key !== false) {
            unset($typeArray[$key]);
        }

        if (!$exclude) {
            $typeArray[] = '' . $type;
            $typeArray = array_unique($typeArray);
        }

        $this->types = implode(',', $typeArray);
    }

    /**
     * Get the types in string value
     *
     * @return string
     */
    public function getTypes(): string
    {
        return $this->types;
    }

    /**
     * @return string
     */
    public function getCursor(): string
    {
        return $this->cursor;
    }

    /**
     * @param string $cursor
     */
    public function setCursor(string $cursor): void
    {
        $this->cursor = $cursor;
    }

}