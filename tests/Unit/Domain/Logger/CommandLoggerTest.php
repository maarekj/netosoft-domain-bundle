<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain\Logger;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\CommandLoggerInterface;
use Netosoft\DomainBundle\Domain\Logger\CommandLogger;
use Netosoft\DomainBundle\Domain\Logger\Annotation\CommandLogger as CommandLoggerAnnotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AbstractCommandFixutre implements CommandInterface
{
    public $id;
    protected $returnValue;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getReturnValue()
    {
        return $this->returnValue;
    }

    public function setReturnValue($returnValue)
    {
        $this->returnValue = $returnValue;

        return $this;
    }
}

class CommandFixtureA extends AbstractCommandFixutre
{
}

/**
 * @CommandLoggerAnnotation()
 */
class CommandFixtureB extends AbstractCommandFixutre
{
}

/**
 * @CommandLoggerAnnotation(service="logger_c")
 */
class CommandFixtureC extends AbstractCommandFixutre
{
}

class FallbackLogger implements CommandLoggerInterface
{
    /**
     * @param CommandInterface|AbstractCommandFixutre $command
     *
     * @return array
     */
    public function log(CommandInterface $command): array
    {
        return ['fallback' => $command->id];
    }
}

class LoggerC implements CommandLoggerInterface
{
    /**
     * @param CommandInterface|AbstractCommandFixutre $command
     *
     * @return array
     */
    public function log(CommandInterface $command): array
    {
        return ['c' => $command->id];
    }
}

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
