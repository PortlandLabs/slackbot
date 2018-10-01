<?php
namespace PortlandLabs\Slackbot\Slack\Api\Payload;

use CL\Slack\Payload\PayloadInterface;
use CL\Slack\Serializer\PayloadResponseSerializer;
use CL\Slack\Serializer\PayloadSerializer;
use JMS\Serializer\SerializerBuilder;

class ResponseSerializer extends PayloadResponseSerializer
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

    /**
     * @param array  $payloadResponse
     * @param string $payloadResponseClass
     *
     * @return PayloadResponseInterface
     */
    public function deserialize(array $payloadResponse, $payloadResponseClass)
    {
        $this->fixSerializer();
        return parent::deserialize($payloadResponse, $payloadResponseClass);
    }

    private function getParentDirectory()
    {
        $reflectioon = new \ReflectionClass(PayloadSerializer::class);
        return \dirname($reflectioon->getFileName());
    }

}