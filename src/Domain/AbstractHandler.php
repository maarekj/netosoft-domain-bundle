<?php

namespace Netosoft\DomainBundle\Domain;

/**
 * Class AbstractHandler.
 */
abstract class AbstractHandler implements HandlerInterface
{
    /** @var string */
    protected $acceptedCommandClass;

    /**
     * AbstractHandler constructor.
     *
     * @param string $acceptedCommandClass
     */
    public function __construct(string $acceptedCommandClass)
    {
        $this->acceptedCommandClass = $acceptedCommandClass;
    }

    /**
     * @param CommandInterface $command
     *
     * @return bool Return true if this handler accept the command
     */
    public function acceptCommand(CommandInterface $command): bool
    {
        return $command instanceof $this->acceptedCommandClass;
    }

    /**
     * @param CommandInterface $command
     *
     * @return mixed
     */
    public function handle(CommandInterface $command)
    {
        /* @noinspection PhpUndefinedMethodInspection */
        return $this->handleCommand($command);
    }
}
