<?php

namespace Netosoft\DomainBundle\Action;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ActionHelper
{
    /** @var \Twig\Environment */
    private $twig;

    /** @var BreadcrumbsBuilderInterface */
    private $breadcrumbsBuilder;

    /** @var Pool */
    private $pool;

    /** @var TranslatorInterface */
    private $translator;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var Session */
    private $session;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    public function __construct(\Twig\Environment $twig, BreadcrumbsBuilderInterface $breadcrumbsBuilder, Pool $pool, TranslatorInterface $translator, FormFactoryInterface $formFactory, Session $session, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->twig = $twig;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->pool = $pool;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function getAdminObjectOrNotFound(Request $request, AdminInterface $admin)
    {
        $id = $request->get($admin->getIdParameter());
        $object = $admin->getObject($id);

        if (null === $object) {
            throw new NotFoundHttpException(\sprintf('unable to find the object with id : %s', $id));
        }

        return $object;
    }

    public function isXmlHttpRequest(Request $request): bool
    {
        return $request->isXmlHttpRequest() || (bool) $request->get('_xml_http_request');
    }

    /**
     * Returns the base template name.
     *
     * @return string The template name
     */
    protected function getBaseTemplate(Request $request, AdminInterface $admin): string
    {
        $template = null;
        if ($this->isXmlHttpRequest($request)) {
            $template = $admin->getTemplate('ajax');
            if (null === $template) {
                throw new \InvalidArgumentException("The template ajax doesn't exist");
            }
        } else {
            $template = $admin->getTemplate('layout');
            if (null === $template) {
                throw new \InvalidArgumentException("The template layout doesn't exist");
            }
        }

        return $template;
    }

    public function adminRender(Request $request, AdminInterface $admin, $view, array $parameters = [], Response $response = null): Response
    {
        if (!$this->isXmlHttpRequest($request)) {
            $parameters['breadcrumbs_builder'] = $this->breadcrumbsBuilder;
        }

        $parameters['admin'] = isset($parameters['admin']) ? $parameters['admin'] : $admin;

        $parameters['base_template'] = isset($parameters['base_template']) ? $parameters['base_template'] : $this->getBaseTemplate($request, $admin);

        $parameters['admin_pool'] = $this->pool;

        return $this->render($view, $parameters, $response);
    }

    public function render($view, array $parameters = [], Response $response = null): Response
    {
        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($this->twig->render($view, $parameters));

        return $response;
    }

    public function addTrFlash(string $flashKey, string $translationKey, array $parameters = [], $domain = null, $locale = null): void
    {
        $message = $this->translator->trans($translationKey, $parameters, $domain, $locale);
        $this->addFlash($flashKey, $message);
    }

    public function addFlash(string $flashKey, string $message): void
    {
        $this->session->getFlashBag()->add($flashKey, $message);
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type    The fully qualified class name of the form type
     * @param mixed  $data    The initial data for the form
     * @param array  $options Options for the form
     *
     * @return FormInterface
     */
    public function createForm(string $type, $data = null, array $options = []): FormInterface
    {
        return $this->formFactory->create($type, $data, $options);
    }

    /**
     * Creates and returns a form builder instance.
     *
     * @param mixed $data    The initial data for the form
     * @param array $options Options for the form
     *
     * @return FormBuilderInterface
     */
    public function createFormBuilder($data = null, array $options = []): FormBuilderInterface
    {
        return $this->formFactory->createBuilder(FormType::class, $data, $options);
    }

    public function createAdminFormView(FormInterface $form, $theme): FormView
    {
        $formView = $form->createView();

        // BC for Symfony < 3.2 where this runtime does not exists
        if (!\method_exists(AppVariable::class, 'getToken')) {
            $this->twig->getExtension(FormExtension::class)->renderer->setTheme($formView, $theme);

            return $formView;
        }

        // BC for Symfony < 3.4 where runtime should be TwigRenderer
        if (!\method_exists(DebugCommand::class, 'getLoaderPaths')) {
            $this->twig->getRuntime(TwigRenderer::class)->setTheme($formView, $theme);

            return $formView;
        }

        $this->twig->getRuntime(FormRenderer::class)->setTheme($formView, $theme);

        return $formView;
    }

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Escape string for html output.
     *
     * @param string $s
     *
     * @return string
     */
    public function escapeHtml($s): string
    {
        return \htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Returns the correct RESTful verb, given either by the request itself or
     * via the "_method" parameter.
     *
     * @return string HTTP method, either
     */
    public function getRestMethod(Request $request): string
    {
        if (Request::getHttpMethodParameterOverride() || !$request->request->has('_method')) {
            return $request->getMethod();
        }

        return $request->request->get('_method');
    }

    /**
     * Get CSRF token.
     *
     * @param string $intention
     *
     * @return string
     */
    public function getCsrfToken($intention): string
    {
        return $this->csrfTokenManager->getToken($intention)->getValue();
    }

    /**
     * Validate CSRF token for action without form.
     *
     * @param string  $intention
     * @param Request $request
     *
     * @throws HttpException
     */
    public function validateCsrfToken(Request $request, $intention): void
    {
        $token = $request->request->get('_sonata_csrf_token', false);

        if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken($intention, $token))) {
            throw new HttpException(400, 'The csrf token is not valid, CSRF attack?');
        }
    }
}
