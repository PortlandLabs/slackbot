<?php
namespace PortlandLabs\Slackbot\Command;

use PortlandLabs\Slackbot\Command\Argument\Manager;
use PortlandLabs\Slackbot\Slack\Rtm\Event\Message;
use Symfony\Component\Console\Helper\Helper;

class InfoCommand extends SimpleCommand
{

    protected $description = 'Get some diagnostic information';

    protected $signature = 'info';

    /**
     * Handle a message
     *
     * @param Message $message
     * @param Manager $manager
     *
     * @return void
     * @throws \CL\Slack\Exception\SlackException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function run(Message $message, Manager $manager)
    {
        $details = [
            'ID' => '`' . $this->bot->getId() . '`',
            'Uptime' => '_' . $this->bot->getUptime() . '_',
            'Memory Usage' => Helper::formatMemory(memory_get_usage(true)),
            'Peak Usage' => Helper::formatMemory(memory_get_peak_usage(true)),
            'userId' => $this->bot->rtm()->getUserId(),
            'userName' => $this->bot->rtm()->getUserName()
        ];

        $data = [];
        foreach ($details as $key => $detail) {
            $data[] = "*$key:* $detail";
        }

        $api = $this->bot->api();
        $builder = $api->getBuilder();

        // Send with icon
        $builder->send(implode(PHP_EOL, $data))
            ->to($message->getChannel())->withIcon(':information_source:')
            ->execute($api);
    }
}