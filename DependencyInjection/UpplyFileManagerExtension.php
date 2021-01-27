<?php

namespace Upply\FileManagerBundle\DependencyInjection;

use Gaufrette\Adapter\AzureBlobStorage\BlobProxyFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Upply\FileManagerBundle\Manager\FileManager;

class UpplyFileManagerExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->register('azure_blob_proxy_factory', BlobProxyFactory::class)
            ->setPublic(false)
            ->setArguments([
                $config['azure_blob_url']
            ]);

        $container->register(FileManager::class)
            ->setPublic(true)
            ->setArguments([
                new Reference(ValidatorInterface::class),
                new Reference('gaufrette.upply_filesystem'),
                $config['directories'],
            ]);
    }

    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['KnpGaufretteBundle'])) {
            throw new \Exception('Bundle KnpGaufretteBundle must be enabled');
        }

        $env = $container->getParameter('kernel.environment');

        $configs = $container->getExtensionConfig($this->getAlias());

        $container->prependExtensionConfig('knp_gaufrette', [
            'filesystems' => [
                'upply' => [
                    'adapter' => $configs[0]['adapter'],
                    'alias' => 'upply_filesystem',
                ],
            ],
            'adapters' => [
                'local' => [
                    'local' => [
                        'directory' => $container->getParameter('kernel.project_dir').'/fileshare',
                    ],
                ],
                'azure' => [
                    'azure_blob_storage' => [
                        'blob_proxy_factory_id' => 'azure_blob_proxy_factory',
                        'container_name' => $env,
                        'create_container' => false,
                    ],
                ],
            ],
            'stream_wrapper' => null,
        ]);
    }

    public function getAlias()
    {
        return 'upply_file_manager';
    }
}
