<?php
namespace PortlandLabs\Slackbot\Command;

use PortlandLabs\Slackbot\Command\Argument\Manager;
use PortlandLabs\Slackbot\Slack\Api\Payload\ReactionsAddPayload;
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
        $extensions = [];
        foreach (get_loaded_extensions() as $extension) {
            $extensions[] = '`' . $extension . '@' . phpversion($extension) . '`';
        }
        
        $details = [
            'ID' => '`' . $this->bot->getId() . '`',
            'Uptime' => '_' . $this->bot->getUptime() . '_',
            'Memory Usage' => Helper::formatMemory(memory_get_usage(true)),
            'Peak Usage' => Helper::formatMemory(memory_get_peak_usage(true)),
            'userId' => $this->bot->rtm()->getUserId(),
            'userName' => $this->bot->rtm()->getUserName(),
            'PHP Version' => phpversion(),
            'extensions' => implode(', ', $extensions),
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

        $this->bot->api()->send(ReactionsAddPayload::reactTo($message, 'the_horns'));
    }
}
