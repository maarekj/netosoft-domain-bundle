<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain\Logger\Fixture;

use Netosoft\DomainBundle\Domain\CommandInterface;

class AbstractCommandFixture implements CommandInterface
{
    public $id;
    protected $returnValue;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getReturnValue()
    {
        return $this->returnValue;
    }

    public function setReturnValue($returnValue)
    {
        $this->returnValue = $returnValue;

        return $this;
    }
}
