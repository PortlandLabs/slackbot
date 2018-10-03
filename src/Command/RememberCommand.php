<?php


namespace PortlandLabs\Slackbot\Command;


use Illuminate\Support\Str;
use PortlandLabs\Slackbot\Bot;
use PortlandLabs\Slackbot\Command\Argument\Manager;
use PortlandLabs\Slackbot\Permission\Admin;
use PortlandLabs\Slackbot\Permission\Checker;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\CacheException;

class RememberCommand extends SimpleCommand
{

    protected $description = 'Remember something interesting to be `recall`ed later';

    protected $signature = 'remember {thing} {value}';

    /** @var CacheInterface */
    protected $cache;

    protected $role = Admin::class;

    public function __construct(Bot $bot, Manager $argumentManager, Checker $checker, CacheInterface $cache)
    {
        parent::__construct($bot, $argumentManager, $checker);

        $this->cache = $cache;
        $this->checker = $checker;
    }

    /**
     * @param Message $message
     * @param Manager $manager
     */
    public function run(Message $message, Manager $manager)
    {
        $thing = $manager->get('thing');
        $text = $message->getText();
        $text = substr($text, strpos($text, '>'));

        $value = trim(substr($text, strpos($text, $thing) + strlen($thing)));

        // trim off quotes
        $value = ltrim($value, '"\'');

        $key = Str::snake('remember ' . $thing);

        try {
            $this->cache->set($key, $value);
            $keys = $this->cache->get('rememberindex', []);
            $keys[$key] = $thing;
            $this->cache->set('rememberindex', $keys);
        } catch (CacheException $e) {
            $this->bot->logger()->error('[CMD.RMB] Failed to remember: ' . $e->getMessage());
            $this->bot->feignTyping($message->getChannel(), 'Hmm.. I wasn\'t able to remember that... What was it again?');
            return;
        }

        $this->bot->feignTyping($message->getChannel(), 'Okay, I\'ve got that remembered! Use the `recall` command to recall.');
    }
}