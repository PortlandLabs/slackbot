<?php
namespace PortlandLabs\Slackbot\Slack;

use Carbon\Carbon;
use Mockery as M;
use PortlandLabs\Slackbot\Bot;
use PortlandLabs\Slackbot\TestCase;

class BotTest extends TestCase
{

    /**
     * @dataProvider uptimeDates
     */
    public function testUptimeString($expected, \DateTime $connected, \DateTime $now)
    {
        $property = (new \ReflectionClass(Bot::class))->getProperty('connected');
        $property->setAccessible(true);

        /** @var Bot|M\MockInterface $bot */
        $bot = M::mock(Bot::class)->makePartial();
        $property->setValue($bot, $connected);

        $this->assertEquals($expected, $bot->getUptime($now));
    }

    public function uptimeDates()
    {
        $time = new Carbon();
        return [
            // Never 0
            ['1 second', $time, $time],
            ['2 days', $time->copy()->subDays(2), $time],
            ['1 week', $time->copy()->subDays(7), $time],
            ['2 days and 5 hours', $time->copy()->subDays(2)->subHours(5), $time],
            ['2 years 5 hours and 2 seconds', $time->copy()->subYear(2)->subHours(5)->subSeconds(2), $time],
            [
                // Make sure it only shows 6 segments
                '123 years 4 months 3 weeks 4 days 15 hours and 10 minutes',
                $time->copy()->subYear(123)->subMonth(4)->subDays(25)->subHours(15)->subMinutes(10)->subSeconds(22),
                $time
            ],
        ];
    }

}
