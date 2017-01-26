<?php

namespace Netosoft\DomainBundle\Domain\Utils;

use Netosoft\DomainBundle\Domain\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorUtils
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateOrThrow($command, $constraints = null, $group = null)
    {
        $violations = $this->validator->validate($command, $constraints, $group);

        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }
}
