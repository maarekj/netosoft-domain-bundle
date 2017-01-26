<?php

namespace Netosoft\DomainBundle\Domain;

interface CommandInterface
{
    public function getReturnValue();

    /**
     * @param mixed $returnValue
     *
     * @return $this
     */
    public function setReturnValue($returnValue);
}
