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

    protected $description = 'Recall things that have been `remember`ed';

    protected $signature = 'recall {thing?} {--l|list : List the things you can recall}';

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
     *
     * @throws \CL\Slack\Exception\SlackException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function run(Message $message, Manager $manager)
    {
        if ($manager->get('list')) {
            $this->outputList($message);
            return;
        }

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

    protected function outputList(Message $message)
    {
        $out = [];
        foreach ($this->cache->get('rememberindex', []) as $key => $thing) {
            $out[] = "`$thing`";
        }

        $last = null;
        if (count($out) > 1) {
            $last = array_pop($out);
        }

        $result = 'I can recall ' . implode(', ', $out);

        if ($last) {
            $result .= ' or ' . $last;
        }

        $this->bot->feignTyping($message->getChannel(), $result);
    }
}