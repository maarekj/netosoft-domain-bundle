<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Netosoft\DomainBundle\Domain\LogHandler;
use Netosoft\DomainBundle\Entity\CommandLogInterface;
use Netosoft\DomainBundle\Entity\CommandLogRepositoryInterface;
use Psr\Log\LoggerInterface;
use Tests\Unit\Netosoft\DomainBundle\Fixture\Command1;
use Tests\Unit\Netosoft\DomainBundle\Fixture\Command1Handler;
use Tests\Unit\Netosoft\DomainBundle\Fixture\Command2;
use Tests\Unit\Netosoft\DomainBundle\Fixture\Command2Handler;
use Tests\Unit\Netosoft\DomainBundle\Fixture\Command3;

class LogHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $manager;

    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var CommandLogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $repo;

    /** @var Command1Handler|\PHPUnit_Framework_MockObject_MockObject */
    private $decoratedHandler;

    /** @var LogHandler */
    private $handler;

    public function setUp()
    {
        $this->manager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->repo = $this->createMock(CommandLogRepositoryInterface::class);
        $this->decoratedHandler = $this->createPartialMock(Command1Handler::class, ['handle']);

        $this->handler = new LogHandler($this->manager, $this->logger, $this->repo);
        $this->handler->setDecoratedHandler($this->decoratedHandler);
    }

    public function testAcceptCommand()
    {
        $this->assertTrue($this->handler->acceptCommand(new Command1()));
        $this->assertFalse($this->handler->acceptCommand(new Command2()));
        $this->assertFalse($this->handler->acceptCommand(new Command3()));

        $this->handler->setDecoratedHandler(new Command2Handler());
        $this->assertFalse($this->handler->acceptCommand(new Command1()));
        $this->assertTrue($this->handler->acceptCommand(new Command2()));
        $this->assertFalse($this->handler->acceptCommand(new Command3()));
    }

    public function testHandle_withSuccess()
    {
        $command = new Command1();

        $this->repo
            ->expects($this->at(0))
            ->method('createEntity')
            ->with($command, CommandLogInterface::TYPE_BEFORE_HANDLER, null, null)
            ->willReturn($beforeLog = $this->mockCommandLog('before'));

        $this->repo
            ->expects($this->at(1))
            ->method('createEntity')
            ->with($command, CommandLogInterface::TYPE_AFTER_HANDLER, $beforeLog, null)
            ->willReturn($afterLog = $this->mockCommandLog('after'));

        $this->decoratedHandler->expects($this->once())->method('handle')->with($command);

        $this->manager->expects($this->at(0))->method('persist')->with($beforeLog);
        $this->manager->expects($this->at(1))->method('flush');
        $this->manager->expects($this->at(2))->method('persist')->with($afterLog);
        $this->manager->expects($this->at(3))->method('flush');

        $this->logger->expects($this->at(0))->method('info')->with('before', ['command' => ['message' => 'before'], 'type' => CommandLogInterface::TYPE_BEFORE_HANDLER]);
        $this->logger->expects($this->at(1))->method('info')->with('after', ['command' => ['message' => 'after'], 'type' => CommandLogInterface::TYPE_AFTER_HANDLER]);

        $this->handler->handle($command);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage exception message
     */
    public function testHandle_withError()
    {
        $command = new Command1();

        $this->decoratedHandler
            ->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($exception = new \InvalidArgumentException('exception message'));

        $this->repo
            ->expects($this->at(0))
            ->method('createEntity')
            ->with($command, CommandLogInterface::TYPE_BEFORE_HANDLER, null, null)
            ->willReturn($beforeLog = $this->mockCommandLog('before'));

        $this->repo
            ->expects($this->at(1))
            ->method('createEntity')
            ->with($command, CommandLogInterface::TYPE_EXCEPTION, $beforeLog, $exception)
            ->willReturn($afterLog = $this->mockCommandLog('exception'));

        $this->manager->expects($this->at(0))->method('persist')->with($beforeLog);
        $this->manager->expects($this->at(1))->method('flush');
        $this->manager->expects($this->at(2))->method('persist')->with($afterLog);
        $this->manager->expects($this->at(3))->method('flush');

        $this->logger->expects($this->at(0))->method('info')->with('before', ['command' => ['message' => 'before'], 'type' => CommandLogInterface::TYPE_BEFORE_HANDLER]);
        $this->logger->expects($this->at(1))->method('error')->with('exception', ['command' => ['message' => 'exception'], 'type' => CommandLogInterface::TYPE_EXCEPTION, 'exception' => $exception]);

        $this->handler->handle($command);
    }

    public function mockCommandLog(string $message) {
        return $this->createConfiguredMock(CommandLogInterface::class, [
            'getMessage' => $message,
            'getCommandData' => ['message' => $message],
        ]);
    }
}
