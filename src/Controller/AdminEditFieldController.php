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

        if ($request->getMethod() != 'POST') {
            return new JsonResponse(['status' => 'KO', 'message' => 'Expected a POST Request']);
        }

        $rootObject = $object = $admin->getObject($objectId);

        if (!$object) {
            return new JsonResponse(['status' => 'KO', 'message' => 'Object does not exist']);
        }

        $fieldDescription = $admin->getListFieldDescription($field);

        $command = new $commandClass($object, $value);

        try {
            $this->getCommandHandler()->handle($command);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => 'KO', 'message' => $exception->getMessage()]);
        }

        $extension = $this->getTwig()->getExtension(SonataAdminExtension::class);
        $content = $extension->renderListElement($this->getTwig(), $rootObject, $fieldDescription);

        return new JsonResponse(['status' => 'OK', 'content' => $content]);
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
