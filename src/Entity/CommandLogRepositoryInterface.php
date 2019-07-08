<?php

namespace Netosoft\DomainBundle\Entity;

use Netosoft\DomainBundle\Domain\CommandInterface;

/**
 * CommandLogRepositoryInterface.
 */
interface CommandLogRepositoryInterface
{
    public function createEntity(CommandInterface $command, int $type, ?CommandLogInterface $previousCommandLog = null, ?\Throwable $exception = null): CommandLogInterface;

    public function getChoicesForCommandClass(): array;

    public function getChoicesForType(): array;
}
