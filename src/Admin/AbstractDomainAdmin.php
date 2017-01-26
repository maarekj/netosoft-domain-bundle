<?php

namespace Netosoft\DomainBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

abstract class AbstractDomainAdmin extends AbstractAdmin
{
    protected $domainConfigs = [];

    public function configure()
    {
        $this->setTemplate('form_command', 'NetosoftDomainBundle::form_command.html.twig');
        $this->setTemplate('create', 'NetosoftDomainBundle::form_command.html.twig');
        $this->setTemplate('edit', 'NetosoftDomainBundle::form_command.html.twig');
    }

    public function setDomainConfig(string $action, array $config)
    {
        $this->domainConfigs[$action] = $config;

        return $this;
    }

    public function getDomainConfig(string $action): array
    {
        return isset($this->domainConfigs[$action]) ? $this->domainConfigs[$action] : [];
    }
}
