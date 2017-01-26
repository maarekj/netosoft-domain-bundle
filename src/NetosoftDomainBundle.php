<?php

namespace Netosoft\DomainBundle;

use Netosoft\DomainBundle\DependencyInjection\Compiler\DomainHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetosoftDomainBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DomainHandlerPass());
    }
}
