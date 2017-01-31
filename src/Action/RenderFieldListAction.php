<?php

namespace Netosoft\DomainBundle\Action;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RenderFieldListAction
{
    /** @var \Twig_Environment */
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param string         $id
     * @param string         $field
     * @param AdminInterface $admin
     *
     * @return Response
     */
    public function handle(string $id, string $field, AdminInterface $admin)
    {
        $object = $admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $fieldDescription = $admin->getListFieldDescription($field);

        $extension = $this->twig->getExtension(SonataAdminExtension::class);
        $content = $extension->renderListElement($this->twig, $object, $fieldDescription);

        return new Response($content);
    }
}
