<?php

namespace Netosoft\DomainBundle\Action;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class RenderRowAction
{
    /** @var Environment */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param string         $id
     * @param AdminInterface $admin
     *
     * @return Response
     */
    public function handle(string $id, AdminInterface $admin)
    {
        $object = $admin->getObject($id);
        if (null === $object) {
            throw new NotFoundHttpException(\sprintf('unable to find the object with id : %s', $id));
        }

        $template = $admin->getTemplate('inner_list_row');
        if (null === $template) {
            throw new \InvalidArgumentException("The template \"inner_list_row\" doesn't exist");
        }

        $content = $this->twig->render($template, ['admin' => $admin, 'object' => $object]);

        return new Response($content);
    }
}
