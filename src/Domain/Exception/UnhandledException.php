<?php

namespace Netosoft\DomainBundle\Domain\Exception;

use Exception;
use Netosoft\DomainBundle\Domain\CommandInterface;

class UnhandledException extends DomainException
{
    /** @var CommandInterface */
    protected $command;

    public function __construct(CommandInterface $command, $code = 0, Exception $previous = null)
    {
        $message = \sprintf('This command %s is unhandled.', \get_class($command));
        $this->command = $command;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return CommandInterface
     */
    public function getCommand(): CommandInterface
    {
        return $this->command;
    }
}
