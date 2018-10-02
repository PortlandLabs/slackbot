<?php
namespace PortlandLabs\Slackbot;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{

    use MockeryPHPUnitIntegration;

}