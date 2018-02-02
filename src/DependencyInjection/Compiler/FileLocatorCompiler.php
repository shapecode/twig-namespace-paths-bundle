<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler;

use Shapecode\Bundle\TwigNamespacePathsBundle\Locator\LiipFileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class FileLocatorCompiler
 *
 * @package Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class FileLocatorCompiler implements CompilerPassInterface
{

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $chain = $container->getDefinition('shapecode_twig_namespace.locator.chain_file_locator');
        $tags = $container->findTaggedServiceIds('shapecode.template_file_locator');

        foreach ($tags as $id => $configs) {
            $chain->addMethodCall('addFileLocator', [new Reference($id)]);
        }

        if ($container->has('liip_theme.file_locator')) {
            $liipLocator = $container->getDefinition('liip_theme.file_locator');
            $liipLocator->setClass(LiipFileLocator::class);

            $chain->addMethodCall('addFileLocator', [new Reference('liip_theme.file_locator')]);
        }
    }

}
