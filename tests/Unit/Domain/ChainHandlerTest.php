<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain;

use Netosoft\DomainBundle\Domain\ChainHandler;
use Tests\Unit\Netosoft\DomainBundle\Fixture\Command1Handler;
use Tests\Unit\Netosoft\DomainBundle\Fixture\Command2Handler;
use Tests\Unit\Netosoft\DomainBundle\Fixture\Command1;
use Tests\Unit\Netosoft\DomainBundle\Fixture\Command2;
use Tests\Unit\Netosoft\DomainBundle\Fixture\Command3;

class ChainHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChainHandler */
    private $handler;

    public function setUp()
    {
        $this->handler = new ChainHandler();
    }

    public function testAcceptCommand()
    {
        $this->handler
            ->addHandler(new Command1Handler())
            ->addHandler(new Command2Handler());

        $this->assertTrue($this->handler->acceptCommand(new Command1()));
        $this->assertTrue($this->handler->acceptCommand(new Command2()));
        $this->assertFalse($this->handler->acceptCommand(new Command3()));
    }

    public function testHandle()
    {
        $handler1 = $this->createPartialMock(Command1Handler::class, ['handle']);
        $handler2 = $this->createPartialMock(Command2Handler::class, ['handle']);

        $this->handler->addHandler($handler1)->addHandler($handler2);

        $command = new Command1();

        $handler1->expects($this->at(0))->method('handle')->with($command);
        $handler2->expects($this->never())->method('handle')->with($command);

        $this->handler->handle($command);
    }

    /**
     * @expectedException \Netosoft\DomainBundle\Domain\Exception\UnhandledException
     * @expectedExceptionMessage This command Tests\Unit\Netosoft\DomainBundle\Fixture\Command3
     */
    public function testHandle_withUnhandledCommand()
    {
        $handler1 = $this->createPartialMock(Command1Handler::class, ['handle']);
        $handler2 = $this->createPartialMock(Command2Handler::class, ['handle']);

        $this->handler->addHandler($handler1)->addHandler($handler2);

        $command = new Command3();

        $handler1->expects($this->never())->method('handle')->with($command);
        $handler2->expects($this->never())->method('handle')->with($command);

        $this->handler->handle($command);
    }
}
