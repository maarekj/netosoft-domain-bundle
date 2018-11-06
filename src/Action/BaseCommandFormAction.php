<?php

namespace Netosoft\DomainBundle\Action;

use Netosoft\DomainBundle\Action\ValueObject\BaseCommandFormActionReturn;
use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\HandlerInterface;
use Netosoft\DomainBundle\Form\Object\FormCommandObject;
use Netosoft\DomainBundle\Form\Type\CreateSubmitType;
use Netosoft\DomainBundle\Form\Type\FormCommandType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseCommandFormAction
{
    /** @var OptionsResolver */
    protected $resolver;

    /** @var HandlerInterface */
    private $handler;

    /** @var LoggerInterface */
    private $logger;

    /** @var ActionHelper */
    private $helper;

    public function __construct(HandlerInterface $handler, LoggerInterface $logger, ActionHelper $helper, ResolverHelper $resolverHelper)
    {
        $this->handler = $handler;
        $this->logger = $logger;
        $this->helper = $helper;

        $this->resolver = new OptionsResolver();

        $this->resolver->setRequired('request')->setAllowedTypes('request', Request::class);
        $this->resolver->setRequired('command')->setAllowedTypes('command', CommandInterface::class);

        $resolverHelper->defineCommandForm($this->resolver, 'command_form');
        $resolverHelper->defineCommandFormOptions($this->resolver, 'command_form_options');
        $this->resolver->setDefault('form_action', null)->setAllowedTypes('form_action', ['null', 'string']);

        $this->resolver->setDefault('configure_actions_form', function (FormBuilderInterface $form, $options) {
            $form->add('submit', CreateSubmitType::class);
        });
    }

    /**
     * @param array $options
     *
     * @return BaseCommandFormActionReturn
     */
    public function handle(array $options)
    {
        $return = new BaseCommandFormActionReturn();

        $options = $this->resolver->resolve($options);

        /** @var Request $request */
        $request = $options['request'];

        /** @var CommandInterface $command */
        $command = $options['command'];

        $form = $this->helper->createForm(FormCommandType::class, new FormCommandObject($command), [
            'method' => 'POST',
            'action' => $options['form_action'],
            'command_form' => $options['command_form']($command, $options),
            'command_form_options' => $options['command_form_options']($command, $options),
            'configure_actions_form' => function (FormBuilderInterface $builder) use ($options) {
                $options['configure_actions_form']($builder, $options);
            },
        ]);

        $form->handleRequest($request);

        $return->success = false;
        $return->exception = null;
        $isSubmitted = $form->isSubmitted();
        $isValid = $form->isValid();
        $return->status = !$isSubmitted ? 'default' : (!$isValid ? 'error-form' : 'valid');
        if ($isSubmitted && $isValid) {
            try {
                $this->handler->handle($command);
                $return->success = true;
                $return->status = 'success';
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
                $return->success = false;
                $return->exception = $e;
                $return->status = 'error-exception';
            }
        }

        $return->command = $command;
        $return->form = $form;
        $return->errorForm = $isSubmitted && !$isValid ? $form->getErrors(true, true) : null;

        return $return;
    }
}
