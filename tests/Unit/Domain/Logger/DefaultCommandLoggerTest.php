<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain\Logger;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\Logger\DefaultCommandLogger;
use Netosoft\DomainBundle\Domain\Utils\LoggerUtils;

class DefaultCommandLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testLog()
    {
        $loggerUtils = $this->createMock(LoggerUtils::class);
        $logger = new DefaultCommandLogger($loggerUtils);

        $command = $this->createMock(CommandInterface::class);
        $loggerUtils->expects($this->once())->method('logCommand')->with($command)->willReturn(['ok']);

        $return = $logger->log($command);
        $this->assertEquals(['ok'], $return);
    }
}
