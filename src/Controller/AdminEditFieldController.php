<?php

namespace Netosoft\DomainBundle\Controller;

use Netosoft\DomainBundle\Domain\HandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminEditFieldController extends Controller
{
    /**
     * @Route("/admin/edit-field", name="netosoft_domain_admin_edit_field")
     */
    public function indexAction(Request $request)
    {
        $field = $request->get('field');
        $code = $request->get('code');
        $objectId = $request->get('objectId');
        $value = $request->get('value');
        $commandClass = $request->get('commandClass');

        $admin = $this->getPool()->getInstance($code);
        $admin->setRequest($request);

        if ('POST' != $request->getMethod()) {
            return new JsonResponse('Expected a POST Request', 405);
        }

        $rootObject = $object = $admin->getObject($objectId);

        if (!$object) {
            return new JsonResponse('Object does not exist', 404);
        }

        $fieldDescription = $admin->getListFieldDescription($field);

        $command = new $commandClass($object, $value);

        try {
            $this->getCommandHandler()->handle($command);
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), 405);
        }

        $extension = $this->getTwig()->getExtension(SonataAdminExtension::class);
        $content = $extension->renderListElement($this->getTwig(), $rootObject, $fieldDescription);

        return new JsonResponse($content, 200);
    }

    protected function getTwig(): \Twig_Environment
    {
        return $this->get('twig');
    }

    protected function getPool(): Pool
    {
        return $this->get('sonata.admin.pool');
    }

    protected function getCommandHandler(): HandlerInterface
    {
        return $this->get('netosoft_domain.handler');
    }
}
