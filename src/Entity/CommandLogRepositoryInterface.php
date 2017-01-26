<?php

namespace Netosoft\DomainBundle\Entity;

use Netosoft\DomainBundle\Domain\CommandInterface;

/**
 * CommandLogRepositoryInterface.
 */
interface CommandLogRepositoryInterface
{
    /**
     * @param CommandInterface         $command
     * @param int                      $type
     * @param CommandLogInterface|null $previousCommandLog
     * @param \Throwable|null          $exception
     *
     * @return CommandLogInterface
     */
    public function createEntity(CommandInterface $command, int $type, CommandLogInterface $previousCommandLog = null, \Throwable $exception = null);

    public function getChoicesForCommandClass();

    public function getChoicesForType(): array;
}
