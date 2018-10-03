<?php
namespace PortlandLabs\Slackbot\Slack\Rtm\Event;

use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class Factory
{

    use LoggerAwareTrait;

    /**
     * A map of types this factory can build
     */
    private const TYPES = [
        'hello' => Hello::class,
        'pong' => Pong::class,
        'message' => Message\UserMessage::class,
    ];

    private const SHORT_MAP = [
        'ts' => 'timestamp'
    ];

    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->setLogger($logger);
    }

    /**
     * Generate a new Event instance based on message data
     *
     * @param array $data
     *
     * @return Event|null
     */
    public function buildEvent(array $data)
    {
        // If the type isn't set, this likely isn't an event at all
        if (!isset($data['type'])) {
            return null;
        }

        // Find the type class
        $typeKey = $this->getMapKey($data);
        $typeClass = self::TYPES[$typeKey] ?? null;

        // If we can't build it, log and return null
        if (!$typeClass) {
            $this->logger->debug(sprintf('[<bold>RTM.FTY</bold>] Missing event class for type "%s" "%s"', $typeKey, $typeClass));
            return null;
        }

        // Build the event
        $event = $this->container->get($typeClass);
        unset($data['type'], $data['subtype']);

        if ($event instanceof Pong) {
            $event->setData($data);
        }

        foreach ($data as $key => $value) {
            $key = self::SHORT_MAP[$key] ?? $key;
            $setter = camel_case('set_' . $key);

            if (!method_exists($event, $setter)) {
                $this->logger->debug(sprintf('[<bold>RTM.FTY</bold>] Missing event setter for "%s" data "%s"', $typeKey, $key));
                continue;
            }

            $event->{$setter}($value);
        }

        return $event;
    }

    /**
     * Get a key for the type as specified by the data array
     *
     * @example input ['type' => 'foo', 'subtype' => 'bar'], output 'foo.bar'. If the subtype were omitted output would just be 'foo'
     *
     * @param array $data
     *
     * @return string
     */
    protected function getMapKey(array $data)
    {
        $type = $data['type'] ?? '';
        $subtype = $data['subtype'] ?? null;

        // Return type.subtype or type
        return $type . ($subtype ? '.' . $subtype : '');
    }

}