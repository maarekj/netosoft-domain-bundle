<?php

namespace Netosoft\DomainBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DomainHandlerPass implements CompilerPassInterface
{
    /** {@inheritdoc} */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('netosoft_domain.chain_handler')) {
            throw new \RuntimeException('The service netosoft_domain.chain_handler must be defined.');
        }

        $chain = $container->findDefinition('netosoft_domain.chain_handler');
        $taggedServices = $container->findTaggedServiceIds('netosoft_domain.handler');

        foreach ($taggedServices as $id => $tags) {
            $chain->addMethodCall('addHandler', [new Reference($id)]);
            $definition = $container->findDefinition($id);
            $definition->setConfigurator([new Reference('netosoft_domain.handler_configurator'), 'configureHandler']);
        }
    }
}
