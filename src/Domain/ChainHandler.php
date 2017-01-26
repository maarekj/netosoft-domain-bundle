<?php

namespace Netosoft\DomainBundle\Domain;

use Netosoft\DomainBundle\Domain\Exception\UnhandledException;

class ChainHandler implements HandlerInterface
{
    /** @var HandlerInterface[]|array */
    private $handlers;

    /**
     * @param HandlerInterface $handler
     *
     * @return $this
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /** {@inheritdoc} */
    public function acceptCommand(CommandInterface $command): bool
    {
        foreach ($this->handlers as $handler) {
            if ($handler->acceptCommand($command)) {
                return true;
            }
        }

        return false;
    }

    /** {@inheritdoc} */
    public function handle(CommandInterface $command)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->acceptCommand($command)) {
                return $handler->handle($command);
            }
        }

        throw new UnhandledException($command);
    }
}
