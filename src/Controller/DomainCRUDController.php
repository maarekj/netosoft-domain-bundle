<?php

namespace Netosoft\DomainBundle\Controller;

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
        $action = $this->get('netosoft_domain.action.delete');

        return $action->handle(array_merge([
            'request' => $this->getRequest(),
            'admin' => $this->getAdmin(),
        ], $this->getAdmin()->getDomainConfig('delete')));
    }

    public function createAction()
    {
        $action = $this->get('netosoft_domain.action.admin_command_form');

        return $action->handle(array_merge([
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

    public function editAction($id = null)
    {
        $action = $this->get('netosoft_domain.action.admin_command_form');

        return $action->handle(array_merge([
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

    public function fieldFormAction(Request $request)
    {
        $action = $this->get('netosoft_domain.action.edit_field_form');
        $field = $request->get('field');

        return $action->handle(array_merge([
            'request' => $request,
            'admin' => $this->getAdmin(),
        ], $this->getAdmin()->getFieldForm($field)));
    }

    public function renderFieldListAction(Request $request)
    {
        $action = $this->get('netosoft_domain.action.render_field_list');
        $id = $request->get($this->getAdmin()->getIdParameter());
        $field = $request->get('field');

        return $action->handle($id, $field, $this->getAdmin());
    }

    public function getAdmin(): AbstractDomainAdmin
    {
        if (!$this->admin instanceof AbstractDomainAdmin) {
            throw new \RuntimeException('The admin must be an instance of AbstractDomainAdmin');
        }

        return $this->admin;
    }
}
