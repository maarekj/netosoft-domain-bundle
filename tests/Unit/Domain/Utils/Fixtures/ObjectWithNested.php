<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain\Utils\Fixtures;

use Netosoft\DomainBundle\Domain\Logger\Annotation\LogFields;

class ObjectWithNested
{
    /** @LogFields(fields={"field1", "field2"}) */
    protected $simpleObject;

    protected $field1;

    protected $field2;

    public function __construct(SimpleObject $simpleObject = null, $field1, $field2)
    {
        $this->simpleObject = $simpleObject;
        $this->field1 = $field1;
        $this->field2 = $field2;
    }

    public function getSimpleObject()
    {
        return $this->simpleObject;
    }

    public function getField1()
    {
        return $this->field1;
    }

    public function getField2()
    {
        return $this->field2;
    }
}
