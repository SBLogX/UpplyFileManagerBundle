<?php

namespace Upply\FileManagerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Upply\FileManagerBundle\DependencyInjection\UpplyFileManagerExtension;

class UpplyFileManagerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    public function getContainerExtension()
    {
        return new UpplyFileManagerExtension();
    }
}
