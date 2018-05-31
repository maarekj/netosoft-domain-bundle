<?php

namespace Netosoft\DomainBundle\Domain\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends DomainException
{
    /** @var ConstraintViolationListInterface */
    private $violations;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        $message = \implode("\n", \array_map(function (ConstraintViolationInterface $violation) {
            return \sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
        }, \iterator_to_array($violations)));
        parent::__construct($message);

        $this->violations = $violations;
    }

    /** @return ConstraintViolationListInterface */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
