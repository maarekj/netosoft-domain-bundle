<?php

namespace Netosoft\DomainBundle\Domain;

use Symfony\Component\DependencyInjection\ContainerInterface;

class HandlerConfigurator
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function configureHandler($handler)
    {
        if ($handler instanceof HandlerAwareInterface) {
            $handler->setDomainHandler($this->container->get('netosoft_domain.handler'));
        }
    }
}
