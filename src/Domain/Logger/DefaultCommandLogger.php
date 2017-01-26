<?php

namespace Netosoft\DomainBundle\Domain\Logger;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\CommandLoggerInterface;
use Netosoft\DomainBundle\Domain\Utils\LoggerUtils;

class DefaultCommandLogger implements CommandLoggerInterface
{
    /** @var LoggerUtils */
    private $loggerUtils;

    public function __construct(LoggerUtils $loggerUtils)
    {
        $this->loggerUtils = $loggerUtils;
    }

    /** {@inheritdoc} */
    public function log(CommandInterface $command): array
    {
        return $this->loggerUtils->logCommand($command);
    }
}
