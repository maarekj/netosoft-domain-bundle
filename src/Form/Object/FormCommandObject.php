<?php

namespace Netosoft\DomainBundle\Form\Object;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FormCommandObject
{
    /**
     * @Assert\Valid()
     * @Assert\NotNull()
     */
    private $command;

    public function __construct(?CommandInterface $command)
    {
        $this->command = $command;
    }

    public function getCommand(): ?CommandInterface
    {
        return $this->command;
    }

    public function setCommand(?CommandInterface $command): self
    {
        $this->command = $command;

        return $this;
    }
}
