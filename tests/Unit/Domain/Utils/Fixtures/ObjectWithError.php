<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain\Utils\Fixtures;

use Netosoft\DomainBundle\Domain\Logger\Annotation\LogFields;
use Netosoft\DomainBundle\Domain\Logger\Annotation\LogMessage;

/**
 * @LogMessage(expression="'object_with_error'")
 */
class ObjectWithError
{
    /** @LogFields(fields={"field1", "erroronpath.field1"}) */
    protected $object;

    protected $field1;

    protected $field2;

    public function __construct(ObjectWithNested $object = null, $field1, $field2)
    {
        $this->object = $object;
        $this->field1 = $field1;
        $this->field2 = $field2;
    }

    public function getObject()
    {
        return $this->object;
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
