<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain\Utils\Fixtures;

use Netosoft\DomainBundle\Domain\Logger\Annotation\LogMessage;

/**
 * @LogMessage(expression="error.onExpression(r) ~ 'error'")
 */
class SimpleObject
{
    protected $field1;
    protected $field2;

    public function __construct($field1, $field2)
    {
        $this->field1 = $field1;
        $this->field2 = $field2;
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
