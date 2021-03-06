<?php

namespace Netosoft\DomainBundle\Controller;

use Netosoft\DomainBundle\Action\AdminCommandFormAction;
use Netosoft\DomainBundle\Action\DeleteAction;
use Netosoft\DomainBundle\Action\EditFieldFormAction;
use Netosoft\DomainBundle\Action\RenderFieldListAction;
use Netosoft\DomainBundle\Action\RenderRowAction;
use Netosoft\DomainBundle\Admin\AbstractDomainAdmin;
use Netosoft\DomainBundle\Form\Type\CreateSubmitType;
use Netosoft\DomainBundle\Form\Type\EditSubmitType;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

class DomainCRUDController extends CRUDController
{
    public function deleteAction($id)
    {
        $action = $this->get(DeleteAction::class);

        return $action->handle(\array_merge([
            'request' => $this->getRequest(),
            'admin' => $this->getAdmin(),
        ], $this->getAdmin()->getDomainConfig('delete')));
    }

    public function createAction()
    {
        $action = $this->get(AdminCommandFormAction::class);

        return $action->handle(\array_merge([
            'request' => $this->getRequest(),
            'admin' => $this->getAdmin(),
            'action' => 'create',
            'template_key' => 'create',
            'box_title' => 'box.create_title',
            'flash_success' => 'flash_create_success',
            'success_response' => 'redirect_edit',
            'configure_actions_form' => function (FormBuilderInterface $form, $options, $args) {
                $form->add('submit', CreateSubmitType::class);
            },
        ], $this->getAdmin()->getDomainConfig('create')));
    }

    public function domainCreateAction(Request $request)
    {
        $action = $this->get(AdminCommandFormAction::class);

        return $action->handle(\array_merge([
            'request' => $this->getRequest(),
            'admin' => $this->getAdmin(),
            'action' => 'create',
            'template_key' => 'create',
            'box_title' => 'box.create_title',
            'flash_success' => 'flash_create_success',
            'success_response' => 'redirect_edit',
            'configure_actions_form' => function (FormBuilderInterface $form, $options, $args) {
                $form->add('submit', CreateSubmitType::class);
            },
        ], $this->getAdmin()->getDomainConfig($request->get('action'))));
    }

    public function editAction($id = null)
    {
        $action = $this->get(AdminCommandFormAction::class);

        return $action->handle(\array_merge([
            'request' => $this->getRequest(),
            'admin' => $this->getAdmin(),
            'action' => 'edit',
            'template_key' => 'edit',
            'box_title' => 'box.edit_title',
            'get_object' => 'from_request',
            'flash_success' => 'flash_edit_success',
            'success_response' => 'redirect_edit',
            'configure_actions_form' => function (FormBuilderInterface $form, $options, $args) {
                $form->add('submit', EditSubmitType::class);
            },
        ], $this->getAdmin()->getDomainConfig('edit')));
    }

    public function domainAction(Request $request)
    {
        $action = $this->get(AdminCommandFormAction::class);

        return $action->handle(\array_merge([
            'request' => $this->getRequest(),
            'admin' => $this->getAdmin(),
            'action' => 'edit',
            'template_key' => 'edit',
            'box_title' => 'box.edit_title',
            'get_object' => 'from_request',
            'flash_success' => 'flash_edit_success',
            'success_response' => 'redirect_edit',
            'configure_actions_form' => function (FormBuilderInterface $form, $options, $args) {
                $form->add('submit', EditSubmitType::class);
            },
        ], $this->getAdmin()->getDomainConfig($request->get('action'))));
    }

    public function fieldFormAction(Request $request)
    {
        $action = $this->get(EditFieldFormAction::class);
        $field = $request->get('field');

        return $action->handle(\array_merge([
            'request' => $request,
            'admin' => $this->getAdmin(),
        ], $this->getAdmin()->getFieldForm($field)));
    }

    public function renderFieldListAction(Request $request)
    {
        $action = $this->get(RenderFieldListAction::class);
        $id = $request->get($this->getAdmin()->getIdParameter());
        $field = $request->get('field');

        return $action->handle($id, $field, $this->getAdmin());
    }

    public function renderRowAction(Request $request)
    {
        $action = $this->get(RenderRowAction::class);
        $id = $request->get($this->getAdmin()->getIdParameter());

        return $action->handle($id, $this->getAdmin());
    }

    public function getAdmin(): AbstractDomainAdmin
    {
        if (!$this->admin instanceof AbstractDomainAdmin) {
            throw new \RuntimeException('The admin must be an instance of AbstractDomainAdmin');
        }

        return $this->admin;
    }
}
