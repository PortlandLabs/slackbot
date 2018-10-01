<?php

namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Model\Attachment;

class AttachmentPayload extends Attachment
{

    protected $ts;

    protected $footer;

    /**
     * Set the timestamp
     *
     * @param $timestamp
     */
    public function setTimestamp(string $timestamp)
    {
        $this->ts = $timestamp;
    }

    /**
     * Get the timestamp
     *
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->ts;
    }

    /**
     * Get the footer
     *
     * @return string
     */
    public function getFooter(): string
    {
        return $this->footer;
    }

    /**
     * Set the footer
     *
     * @param string $footer
     */
    public function setFooter(string $footer)
    {
        $this->footer = $footer;
    }

}
