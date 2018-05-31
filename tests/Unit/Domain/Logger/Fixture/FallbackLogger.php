<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain\Logger\Fixture;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\CommandLoggerInterface;

class FallbackLogger implements CommandLoggerInterface
{
    /**
     * @param CommandInterface|AbstractCommandFixture $command
     *
     * @return array
     */
    public function log(CommandInterface $command): array
    {
        return ['fallback' => $command->id];
    }
}
