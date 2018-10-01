<?php
namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Payload\PayloadInterface;
use CL\Slack\Serializer\PayloadSerializer;
use JMS\Serializer\SerializerBuilder;

class Serializer extends PayloadSerializer
{

    protected $localSerializer;

    protected function fixSerializer()
    {
        if (!$this->localSerializer) {
            $parentMetaDir = $this->getParentDirectory() . '/../Resources/config/serializer';
            $metaDir = realpath(dirname(__DIR__, 4) . '/resources/metadata/');

            $this->serializer = SerializerBuilder::create()
                ->addMetadataDir($parentMetaDir)
                ->addMetadataDir($metaDir, 'PortlandLabs\Slackbot\Slack\Api\Payload')
                ->build();

            $this->localSerializer = true;
        }
    }

    public function serialize(PayloadInterface $payload)
    {
        $this->fixSerializer();
        return parent::serialize($payload);
    }

    private function getParentDirectory()
    {
        $reflectioon = new \ReflectionClass(PayloadSerializer::class);
        return \dirname($reflectioon->getFileName());
    }

}