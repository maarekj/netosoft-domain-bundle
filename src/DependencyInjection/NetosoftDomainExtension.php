<?php

namespace Netosoft\DomainBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class NetosoftDomainExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('netosoft_domain.entity.command_log.class', $config['entity']['command_log']);
        $container->setParameter('netosoft_domain.repository.command_log.class', $config['repository']['command_log']);
        $container->setAlias('netosoft_domain.default_command_logger', $config['default_command_logger']);
        $container->setAlias('netosoft_domain.cache_logger_utils', $config['cache_logger_utils']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
