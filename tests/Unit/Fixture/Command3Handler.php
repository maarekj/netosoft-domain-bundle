<?php

namespace Tests\Unit\Netosoft\DomainBundle\Fixture;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\HandlerInterface;

class Command3Handler implements HandlerInterface
{
    public function acceptCommand(CommandInterface $command): bool
    {
        return $command instanceof Command3;
    }

    public function handle(CommandInterface $command)
    {
        return null;
    }
}