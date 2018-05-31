<?php

namespace Netosoft\DomainBundle\Action;

use Netosoft\DomainBundle\Domain\CommandInterface;
use Netosoft\DomainBundle\Domain\HandlerInterface;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteAction
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

        $this->handler = $handler;
        $this->logger = $logger;
        $this->helper = $helper;

        $this->resolver = new OptionsResolver();

        $this->resolver->setRequired('request')->setAllowedTypes('request', Request::class);
        $this->resolver->setRequired('admin')->setAllowedTypes('admin', AdminInterface::class);

        $resolverHelper->defineCommand($this->resolver, 'command');
        $resolverHelper->defineGetObject($this->resolver, 'get_object', 'from_request');
        $resolverHelper->defineSuccessResponse($this->resolver, 'success_response', 'redirect_list');

        $this->resolver->setDefault('flash_translation_domain', 'SonataAdminBundle');
        $this->resolver->setDefault('flash_success', 'flash_delete_success');

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

        $object = $options['get_object']($options);
        $args['object'] = $object;

        $admin->checkAccess('delete', $object);

        if ('DELETE' === $this->helper->getRestMethod($request)) {
            // check the csrf token
            $this->helper->validateCsrfToken($request, 'sonata.delete');
            $objectName = $options['to_string']($object);

            try {
                /** @var CommandInterface $command */
                $command = $options['command']($options, $args);
                $args['command'] = $args;

                $this->handler->handle($command);

                $args['returned'] = $command->getReturnValue();

                $this->helper->addTrFlash('sonata_flash_success', $options['flash_success'], [
                    '%name%' => $objectName,
                ], $options['flash_translation_domain']);

                return $options['success_response']($options, $args);
            } catch (\Exception $exception) {
                $this->helper->addFlash('sonata_flash_error', \nl2br($exception->getMessage()));
                $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            }
        }

        return $this->helper->adminRender($request, $admin, $admin->getTemplate('delete'), [
            'object' => $object,
            'action' => 'delete',
            'csrf_token' => $this->helper->getCsrfToken('sonata.delete'),
        ]);
    }
}
