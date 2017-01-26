<?php

namespace Netosoft\DomainBundle\Domain\Logger;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\CommandLoggerInterface;

class NoneLogger implements CommandLoggerInterface
{
    /** {@inheritdoc} */
    public function log(CommandInterface $command): array
    {
        return [];
    }
}
