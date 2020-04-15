<?php

namespace Upply\FileManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class UpplyFileManagerBundleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->setParameter('upply_file_manager.blob_storage_url', $config['blob_storage_url']);
        $container->setParameter('upply_file_manager.storage_dirnames', $config['storage_dirnames']);
    }

    public function getAlias()
    {
        return 'upply_file_manager';
    }
}
