<?php

namespace Netosoft\DomainBundle\Action;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\HandlerInterface;
use Netosoft\DomainBundle\Form\Type\CreateSubmitType;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminCommandFormAction
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
        $this->resolver->setRequired('admin')->setAllowedTypes('admin', AdminInterface::class);

        $resolverHelper->defineCommand($this->resolver, 'command');
        $resolverHelper->defineGetObject($this->resolver, 'get_object');
        $resolverHelper->defineSuccessResponse($this->resolver, 'success_response', 'redirect_list');
        $resolverHelper->defineCommandForm($this->resolver, 'command_form');
        $resolverHelper->defineCommandFormOptions($this->resolver, 'command_form_options');

        $this->resolver
            ->setDefault('configure_actions_form', function (FormBuilderInterface $form, $options, $args) {
                $form->add('submit', CreateSubmitType::class);
            });

        $this->resolver->setRequired('action')->setAllowedTypes('action', 'string');
        $this->resolver->setRequired('template_key')->setAllowedTypes('template_key', 'string');

        $this->resolver->setDefault('flash_translation_domain', 'SonataAdminBundle');
        $this->resolver->setDefault('flash_success', 'flash_create_success');

        $this->resolver
            ->setRequired('to_string')
            ->setAllowedTypes('to_string', 'callable')
            ->setDefault('to_string', function (Options $options) {
                return function ($object) use ($options) {
                    /** @var AdminInterface $admin */
                $admin = $options['admin'];

                    return $this->helper->escapeHtml($admin->toString($object));
                };
            });

        $this->resolver
            ->setRequired('box_title')
            ->setAllowedTypes('box_title', 'string')
            ->setDefault('box_title_translation_domain', function (Options $options) {
                /** @var AdminInterface $admin */
                $admin = $options['admin'];

                return $admin->getTranslationDomain();
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

        $object = $options['get_object'] !== null ? $options['get_object']($options) : null;
        $args['object'] = $object;

        /** @var CommandInterface $command */
        $command = $options['command']($options, $args);
        $args['command'] = $args;

        $admin->checkAccess($options['action']);

        $formBuilder = $this->helper->createFormBuilder(['command' => $command])
            ->add('command', $options['command_form']($command, $options, $args), $options['command_form_options']($command, $options, $args))
            ->add('actions', FormType::class);

        $options['configure_actions_form']($formBuilder->get('actions'), $options, $args);

        $formBuilder->setMethod('POST');
        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handler->handle($command);
                $returned = $command->getReturnValue();
                $args['returned'] = $returned;

                if (null !== ($flashSuccess = $options['flash_success'])) {
                    $this->helper->addTrFlash('sonata_flash_success', $options['flash_success'], [
                        '%name%' => $options['to_string']($returned),
                    ], $options['flash_translation_domain']);
                }

                return $options['success_response']($options, $args);
            } catch (\Exception $exception) {
                $this->helper->addFlash('sonata_flash_error', nl2br($exception->getMessage()));
                $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            }
        }

        return $this->helper->adminRender($request, $admin, $admin->getTemplate($options['template_key']), [
            'action' => $options['action'],
            'command' => $command,
            'object' => $object,
            'form' => $this->helper->createAdminFormView($form, $admin->getFormTheme()),
            'box_title' => $this->getBoxTitle($options),
        ]);
    }

    protected function getBoxTitle(array $options): string
    {
        return $this->helper->trans($options['box_title'], [], $options['box_title_translation_domain']);
    }
}
