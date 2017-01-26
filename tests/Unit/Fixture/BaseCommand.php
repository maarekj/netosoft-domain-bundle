<?php

namespace Tests\Unit\Netosoft\DomainBundle\Fixture;

use Netosoft\DomainBundle\Domain\CommandInterface;

class BaseCommand implements CommandInterface
{
    public $id;
    public $returnValue;

    public function __construct($id = null)
    {
        $this->id = $id;
    }


    public function getReturnValue()
    {
        $this->returnValue;
    }

    /**
     * @param mixed $returnValue
     *
     * @return $this
     */
    public function setReturnValue($returnValue)
    {
        $this->returnValue = $returnValue;

        return $this;
    }
}