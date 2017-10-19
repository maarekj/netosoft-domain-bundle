<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain\Logger;

use Netosoft\DomainBundle\Domain\Logger\CommandLogger;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Unit\Netosoft\DomainBundle\Domain\Logger\Fixture\CommandFixtureA;
use Tests\Unit\Netosoft\DomainBundle\Domain\Logger\Fixture\CommandFixtureB;
use Tests\Unit\Netosoft\DomainBundle\Domain\Logger\Fixture\CommandFixtureC;
use Tests\Unit\Netosoft\DomainBundle\Domain\Logger\Fixture\CommandFixtureNotLog;
use Tests\Unit\Netosoft\DomainBundle\Domain\Logger\Fixture\FallbackLogger;
use Tests\Unit\Netosoft\DomainBundle\Domain\Logger\Fixture\LoggerC;

class CommandLoggerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerInterface */
    protected $container;

    /** @var CommandLogger */
    protected $logger;

    public function setUp()
    {
        $this->container = new Container();
        $this->container->set('logger_c', new LoggerC());

        $this->logger = new CommandLogger($this->container, new AnnotationReader(), new FallbackLogger());
    }

    public function testMustLog()
    {
        $this->assertTrue($this->logger->mustLog(new CommandFixtureA(1)));
        $this->assertTrue($this->logger->mustLog(new CommandFixtureB(1)));
        $this->assertTrue($this->logger->mustLog(new CommandFixtureC(1)));
        $this->assertFalse($this->logger->mustLog(new CommandFixtureNotLog(1)));
    }

    /**
     * @dataProvider provideLog
     */
    public function testLog($command, $expected)
    {
        $return = $this->logger->log($command);
        $this->assertEquals($expected, $return);
    }

    public function provideLog()
    {
        yield [new CommandFixtureA(1), ['fallback' => 1]];
        yield [new CommandFixtureA(2), ['fallback' => 2]];
        yield [new CommandFixtureB(1), ['fallback' => 1]];
        yield [new CommandFixtureB(2), ['fallback' => 2]];
        yield [new CommandFixtureC(1), ['c' => 1]];
        yield [new CommandFixtureC(2), ['c' => 2]];
    }
}
