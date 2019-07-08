<?php

namespace Netosoft\DomainBundle\Action;

use Netosoft\DomainBundle\Domain\Command\AbstractEditField;
use Netosoft\DomainBundle\Domain\HandlerInterface;
use Netosoft\DomainBundle\Form\Type\EditSubmitType;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditFieldFormAction
{
    /** @var OptionsResolver */
    protected $resolver;

    /** @var HandlerInterface */
    private $handler;

    /** @var LoggerInterface */
    private $logger;

    /** @var ActionHelper */
    private $helper;

    /** @var \Twig_Environment */
    private $twig;

    public function __construct(HandlerInterface $handler, LoggerInterface $logger, ActionHelper $helper, ResolverHelper $resolverHelper, \Twig_Environment $twig)
    {
        $this->handler = $handler;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->twig = $twig;

        $this->resolver = new OptionsResolver();

        $this->resolver->setRequired('request')->setAllowedTypes('request', Request::class);
        $this->resolver->setRequired('admin')->setAllowedTypes('admin', AdminInterface::class);

        $this->resolver->setRequired('command_class')->setAllowedTypes('command_class', 'string');
        $resolverHelper->defineCommandForm($this->resolver, 'command_form');
        $resolverHelper->defineCommandFormOptions($this->resolver, 'command_form_options');

        $this->resolver->setDefault('modal_template', '@NetosoftDomain/modal_form.html.twig');
        $this->resolver->setDefault('modal_title', 'edit_field_form.modal_title');
        $this->resolver->setDefault('modal_title_translation_domain', 'NetosoftDomainBundle');
        $this->resolver->setDefault('success_message', 'edit_field_form.success_message');
        $this->resolver->setDefault('success_message_translation_domain', 'NetosoftDomainBundle');
        $this->resolver->setDefault('check_access', 'edit')->setAllowedTypes('check_access', 'string');

        $this->resolver->setDefault('configure_actions_form', function (FormBuilderInterface $form, $options, $args) {
            $form->add('submit', EditSubmitType::class);
        });
    }

    /**
     * @param array $options
     *
     * @return Response
     */
    public function handle(array $options)
    {
        $args = [];
        $options = $this->resolver->resolve($options);
        $args['options'] = $options;

        /** @var Request $request */
        $request = $options['request'];

        /** @var AdminInterface $admin */
        $admin = $options['admin'];

        $object = $this->helper->getAdminObjectOrNotFound($request, $admin);
        $args['object'] = $object;

        $admin->checkAccess($options['check_access'], $object);

        /** @var AbstractEditField $command */
        $command = new $options['command_class']($object, null);
        if (!$command instanceof AbstractEditField) {
            throw new \RuntimeException(\sprintf('$command must be instance of %s but instance of %s given.', AbstractEditField::class, \get_class($command)));
        }
        $command->setValue($command->getOldValue());

        $args['command'] = $args;

        $formBuilder = $this->helper->createFormBuilder(['command' => $command])
            ->add('command', $options['command_form']($command, $options, $args), $options['command_form_options']($command, $options, $args))
            ->add('actions', FormType::class);

        $options['configure_actions_form']($formBuilder->get('actions'), $options, $args);

        $formBuilder->setMethod('POST');
        $formBuilder->setAction($request->getUri());
        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $success = false;
        $exception = null;
        $isSubmitted = $form->isSubmitted();
        $isValid = $form->isValid();
        $status = !$isSubmitted ? 'default' : (!$isValid ? 'error-form' : 'valid');
        if ($isSubmitted && $isValid) {
            try {
                $this->handler->handle($command);
                $returned = null;
                if (\method_exists($command, 'getReturnValue')) {
                    $returned = $command->getReturnValue();
                }
                $args['returned'] = $returned;
                $success = true;
                $status = 'success';
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['exception' => $exception]);
                $success = false;
                $exception = $e;
                $status = 'error-exception';
            }
        }

        return $this->wrapJson($status, $this->helper->adminRender($request, $admin, $options['modal_template'], [
            'object' => $object,
            'status' => $status,
            'error_form' => $isSubmitted && !$isValid ? $form->getErrors(true, true) : null,
            'command' => $command,
            'success' => $success,
            'success_message' => $success ? $this->helper->trans($options['success_message'], [], $options['success_message_translation_domain']) : null,
            'exception' => $exception,
            'modal_title' => $this->helper->trans($options['modal_title'], [], $options['modal_title_translation_domain']),
            'form' => $this->helper->createAdminFormView($form, $admin->getFormTheme()),
        ]));
    }

    protected function wrapJson($status, Response $response): JsonResponse
    {
        return new JsonResponse(['status' => $status, 'content' => $response->getContent()]);
    }

    protected function renderColumn($object, FieldDescriptionInterface $fieldDescription)
    {
        $extension = $this->twig->getExtension(SonataAdminExtension::class);
        $content = $extension->renderListElement($this->twig, $object, $fieldDescription);

        return new JsonResponse(['status' => 'OK', 'type' => 'column', 'content' => $content]);
    }
}
