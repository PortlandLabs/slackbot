<?php
namespace PortlandLabs\Slackbot\Slack\Api\Payload\Model;

use CL\Slack\Model\AbstractModel;

class ResponseMetadata extends AbstractModel
{

    protected $nextCursor;

    /**
     * @return mixed
     */
    public function getNextCursor()
    {
        return $this->nextCursor;
    }

    /**
     * @param mixed $nextCursor
     */
    public function setNextCursor($nextCursor): void
    {
        $this->nextCursor = $nextCursor;
    }

}