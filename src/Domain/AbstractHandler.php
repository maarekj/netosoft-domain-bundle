<?php

namespace Netosoft\DomainBundle\Domain;

/**
 * Class AbstractHandler.
 */
abstract class AbstractHandler implements HandlerInterface
{
    /** @var string[] */
    private $acceptedCommandClasses;

    /**
     * AbstractHandler constructor.
     *
     * @param string[]|array|string $acceptedCommandClasses
     */
    public function __construct($acceptedCommandClasses)
    {
        $this->acceptedCommandClasses = (array) $acceptedCommandClasses;
    }

    /**
     * @param CommandInterface $command
     *
     * @return bool Return true if this handler accept the command
     */
    public function acceptCommand(CommandInterface $command): bool
    {
        foreach ($this->acceptedCommandClasses as $class) {
            if (true === $command instanceof $class) {
                return true;
            }
        }

        return false;
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
