<?php

namespace Netosoft\DomainBundle\Domain;

interface HandlerInterface
{
    /**
     * @param CommandInterface $command
     *
     * @return bool Return true if this handler accept the command
     */
    public function acceptCommand(CommandInterface $command): bool;

    /**
     * @param CommandInterface $command
     *
     * @return mixed
     */
    public function handle(CommandInterface $command);
}
