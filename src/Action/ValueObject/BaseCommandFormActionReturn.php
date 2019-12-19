<?php

namespace Netosoft\DomainBundle\Action\ValueObject;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;

final class BaseCommandFormActionReturn
{
    /** @var CommandInterface */
    public $command;

    /** @var FormInterface */
    public $form;

    /** @var bool */
    public $success;

    /** @var \Exception|null */
    public $exception;

    /** @var 'default'|'error-form'|'valid'|'success'|'error-exception' */
    public $status;

    /** @var FormErrorIterator|null */
    public $errorForm;

    /**
     * BaseCommandFormActionReturn constructor.
     *
     * @param CommandInterface $command
     * @param FormInterface    $form
     * @param bool             $success
     * @param \Exception|null  $exception
     */
    public function __construct(CommandInterface $command, FormInterface $form, bool $success)
    {
        $isSubmitted = $form->isSubmitted();
        $isValid = $form->isValid();

        $this->command = $command;
        $this->form = $form;
        $this->success = $success;
        $this->exception = $exception;
        $this->status = !$isSubmitted ? 'default' : (!$isValid ? 'error-form' : 'valid');
        $this->errorForm = $isSubmitted && !$isValid ? $form->getErrors(true, true) : null;
    }
}
