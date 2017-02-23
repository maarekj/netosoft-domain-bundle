<?php

namespace Netosoft\DomainBundle\Action\ValueObject;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;

/**
 * @property CommandInterface $command
 * @property FormInterface $form
 * @property bool $success
 * @property \Exception|null $exception
 * @property string $status
 * @property FormErrorIterator|null $errorForm
 */
class BaseCommandFormActionReturn extends \stdClass
{
}
