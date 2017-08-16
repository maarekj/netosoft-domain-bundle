<?php

namespace Netosoft\DomainBundle\Action;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RenderRowAction
{
    /** @var \Twig_Environment */
    private $twig;

    public function __construct(\Twig_Environment $twig)
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
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $template = $admin->getTemplate('inner_list_row');
        $content = $this->twig->render($template, ['admin' => $admin, 'object' => $object]);

        return new Response($content);
    }
}
