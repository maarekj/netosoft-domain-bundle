<?php

namespace Netosoft\DomainBundle\Action;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\HandlerInterface;
use Netosoft\DomainBundle\Form\Type\CreateSubmitType;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $this->resolver->setDefault('modal_template_key', 'modal')->setAllowedTypes('modal_template_key', 'string');

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
        $args['command'] = $command;

        $admin->checkAccess($options['action'], $object);

        $formBuilder = $this->helper->createFormBuilder(['command' => $command])
            ->add('command', $options['command_form']($command, $options, $args), $options['command_form_options']($command, $options, $args))
            ->add('actions', FormType::class);

        $options['configure_actions_form']($formBuilder->get('actions'), $options, $args);

        $formBuilder->setMethod('POST');
        $formBuilder->setAction($request->getUri());
        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $modeModal = $request->isXmlHttpRequest() || $request->get('mode') === 'modal';
        $flashSuccess = $options['flash_success'];

        $success = false;
        $exception = null;
        $isSubmitted = $form->isSubmitted();
        $isValid = $form->isValid();
        $status = !$isSubmitted ? 'default' : (!$isValid ? 'error-form' : 'valid');
        if ($isSubmitted && $isValid) {
            try {
                $this->handler->handle($command);
                $returned = $command->getReturnValue();
                $args['returned'] = $returned;
                $success = true;
                $status = 'success';

                if (!$modeModal) {
                    if (null !== $flashSuccess) {
                        $this->helper->addTrFlash('sonata_flash_success', $flashSuccess, [
                            '%name%' => $options['to_string']($returned),
                        ], $options['flash_translation_domain']);
                    }

                    return $options['success_response']($options, $args);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
                $success = false;
                $exception = $e;
                $status = 'error-exception';

                if (!$modeModal) {
                    $this->helper->addFlash('sonata_flash_error', nl2br($e->getMessage()));
                }
            }
        }

        $templateParams = [
            'action' => $options['action'],
            'command' => $command,
            'object' => $object,
            'form' => $this->helper->createAdminFormView($form, $admin->getFormTheme()),
            'success' => $success,
            'exception' => $exception,
            'status' => $status,
            'error_form' => $isSubmitted && !$isValid ? $form->getErrors(true, true) : null,
        ];

        if ($modeModal) {
            $templateParams['modal_title'] = $this->getBoxTitle($options);

            if ($success && $flashSuccess !== null) {
                $templateParams['success_message'] = $this->helper->trans($flashSuccess, [
                    '%name%' => $options['to_string']($command->getReturnValue()),
                ], $options['flash_translation_domain']);
            }

            return $this->wrapJson($status, $this->helper->adminRender($request, $admin, $admin->getTemplate($options['modal_template_key']), $templateParams));
        } else {
            $templateParams['box_title'] = $this->getBoxTitle($options);

            return $this->helper->adminRender($request, $admin, $admin->getTemplate($options['template_key']), $templateParams);
        }
    }

    protected function wrapJson($status, Response $response): JsonResponse
    {
        return new JsonResponse(['status' => $status, 'content' => $response->getContent()]);
    }

    protected function getBoxTitle(array $options): string
    {
        return $this->helper->trans($options['box_title'], [], $options['box_title_translation_domain']);
    }
}
