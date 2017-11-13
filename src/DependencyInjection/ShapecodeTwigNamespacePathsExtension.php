<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class ShapecodeTwigNamespacePathsExtension
 *
 * @package Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection
 * @author  Nikita Loges
 */
class ShapecodeTwigNamespacePathsExtension extends Extension
{

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
