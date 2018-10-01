<?php

namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Model\Attachment;
use CL\Slack\Payload\AdvancedSerializeInterface;
use CL\Slack\Payload\ChatUpdatePayloadResponse;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Serializer;

class ChatUpdatePayload extends \CL\Slack\Payload\ChatUpdatePayload implements AdvancedSerializeInterface
{

    /**
     * @var Attachment[]|ArrayCollection
     */
    private $attachments;

    /**
     * @var string
     */
    private $attachmentsJson;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }

    /**
     * @return Attachment[]|ArrayCollection
     */
    public function getAttachments()
    {
        if (is_string($this->attachments)) {
            $this->attachments = new ArrayCollection(json_decode($this->attachments, true));
        }
        return $this->attachments;
    }

    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->getAttachments()->add($attachment);
    }

    /**
     * @inheritdoc
     */
    public function beforeSerialize(Serializer $serializer)
    {
        $this->attachments = $serializer->serialize($this->attachments, 'json');
    }

    /**
     * @inheritdoc
     */
    public function getResponseClass()
    {
        return ChatUpdatePayloadResponse::class;
    }

}
