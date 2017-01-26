<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain\Logger;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\Logger\NoneLogger;

class NoneLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testLog()
    {
        $logger = new NoneLogger();
        $this->assertEquals([], $logger->log($this->createMock(CommandInterface::class)));
        $this->assertEquals([], $logger->log($this->createMock(CommandInterface::class)));
    }
}
