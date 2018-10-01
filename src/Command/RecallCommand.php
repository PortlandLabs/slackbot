<?php
namespace PortlandLabs\Slackbot\Command;

use Illuminate\Support\Str;
use PortlandLabs\Slackbot\Bot;
use PortlandLabs\Slackbot\Command\Argument\Manager;
use PortlandLabs\Slackbot\Permission\Checker;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\CacheException;

class RecallCommand extends SimpleCommand
{

    protected $signature = 'recall {thing}';

    /** @var CacheInterface */
    protected $cache;

    public function __construct(Bot $bot, Manager $argumentManager, Checker $checker, CacheInterface $cache)
    {
        parent::__construct($bot, $argumentManager, $checker);

        $this->cache = $cache;
    }

    /**
     * @param Message $message
     * @param Manager $manager
     */
    public function run(Message $message, Manager $manager)
    {
        $thing = $manager->get('thing');
        $key = Str::snake('remember ' . $thing);

        try {
            $value = $this->cache->get($key);
        } catch (CacheException $e) {
            $this->bot->logger()->error('[CMD.REC] Failed to recall: ' . $e->getMessage());
        }

        if (!$value) {
            $this->bot->feignTyping($message->getChannel(), 'Hmm.. I\'m not able to recall that...');
            return;
        }

        $value = str_replace("\n", "\n> ", $value);
        $api = $this->bot->api();
        $api->getBuilder()
            ->send("Here's what I remember:\n> " . $value)->to($message->getChannel())
            ->withIcon(':thinking_face:')
            ->execute($api);
    }
}