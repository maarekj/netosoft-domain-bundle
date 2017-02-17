<?php

namespace Tests\Unit\Netosoft\DomainBundle\Fixture;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\HandlerInterface;

class Command2Handler implements HandlerInterface
{
    public function acceptCommand(CommandInterface $command): bool
    {
        return $command instanceof Command2;
    }

    public function handle(CommandInterface $command)
    {
        return null;
    }
}
