<?php

namespace Netosoft\DomainBundle\Domain;

interface CommandLoggerInterface
{
    /**
     * @param CommandInterface $command
     *
     * @return array
     */
    public function log(CommandInterface $command): array;
}
