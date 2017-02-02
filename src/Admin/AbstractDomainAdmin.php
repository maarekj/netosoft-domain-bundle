<?php

namespace Netosoft\DomainBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\FormFactoryInterface;

abstract class AbstractDomainAdmin extends AbstractAdmin
{
    /** @var FormFactoryInterface */
    protected $formFactory = null;

    /** @var array */
    protected $domainConfigs = [];

    /** @var array */
    protected $fieldForms = [];

    public function configure()
    {
        $this->setTemplate('form_command', '@NetosoftDomain/form_command.html.twig');
        $this->setTemplate('create', '@NetosoftDomain/form_command.html.twig');
        $this->setTemplate('edit', '@NetosoftDomain/form_command.html.twig');
        $this->setTemplate('modal', '@NetosoftDomain/modal_form.html.twig');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('fieldForm', 'field-form/'.$this->getRouterIdParameter().'/{field}', [], ['field' => '[-_a-zA-Z0-9]+']);
        $collection->add('renderFieldList', 'render-field-list/'.$this->getRouterIdParameter().'/{field}', [], ['field' => '[-_a-zA-Z0-9]+']);
    }

    /**
     * @param string $action The name of action
     * @param array  $config The config of action
     *
     * @return $this
     */
    public function setDomainConfig(string $action, array $config)
    {
        $this->domainConfigs[$action] = $config;

        return $this;
    }

    public function getDomainConfig(string $action): array
    {
        return isset($this->domainConfigs[$action]) ? $this->domainConfigs[$action] : [];
    }

    /**
     * @param string $fieldKey The name of field
     * @param array  $config   The config for action
     *
     * @return $this
     */
    public function setFieldForms(string $fieldKey, array $config)
    {
        $this->fieldForms[$fieldKey] = $config;

        return $this;
    }

    public function getFieldForm(string $fieldKey)
    {
        return isset($this->fieldForms[$fieldKey]) ? $this->fieldForms[$fieldKey] : null;
    }

    /**
     * @return FormFactoryInterface
     */
    public function getFormFactory(): FormFactoryInterface
    {
        if ($this->formFactory === null) {
            throw new \RuntimeException('formFactory must be setted by setter injection.');
        }

        return $this->formFactory;
    }

    /**
     * @param FormFactoryInterface $formFactory
     *
     * @return $this
     */
    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;

        return $this;
    }

    protected function configureBatchActions($actions)
    {
        unset($actions['delete']);

        return $actions;
    }
}
