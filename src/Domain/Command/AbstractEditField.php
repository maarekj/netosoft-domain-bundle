<?php

namespace Netosoft\DomainBundle\Domain\Command;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\Logger\Annotation\LogFields;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractEditField implements CommandInterface
{
    /**
     * @var object
     * @LogFields(fields={"id"})
     */
    protected $returnValue;

    /**
     * @var object
     * @LogFields(fields={"id"})
     */
    protected $entity;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var mixed
     */
    protected $oldValue;

    /**
     * @var array
     */
    protected $validationGroups;

    /**
     * @var array
     */
    protected $securityAttributes;

    /**
     * AbstractEditField constructor.
     *
     * @param object       $entity
     * @param mixed        $value
     * @param string       $field
     * @param array|string $securityAttributes
     * @param array|string $validationGroups
     */
    public function __construct($entity, $value, $field, $securityAttributes, $validationGroups)
    {
        $this->entity = $entity;
        $this->value = $value;
        $this->field = $field;
        $this->securityAttributes = (array) $securityAttributes;
        $this->validationGroups = (array) $validationGroups;

        $accessor = PropertyAccess::createPropertyAccessor();
        $this->oldValue = $accessor->getValue($entity, $field);
    }

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }

    /**
     * @return array
     */
    public function getSecurityAttributes(): array
    {
        return $this->securityAttributes;
    }

    /**
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @return object
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * @param object $returnValue
     *
     * @return $this
     */
    public function setReturnValue($returnValue)
    {
        $this->returnValue = $returnValue;

        return $this;
    }
}
