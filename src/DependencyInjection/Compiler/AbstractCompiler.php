<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AbstractCompiler
 *
 * @package Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler
 * @author  Nikita Loges
 * @company tenolo GbR
 */
abstract class AbstractCompiler
{

    /**
     * @param ContainerBuilder $container
     *
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    protected function getTwigFileSystemLoader(ContainerBuilder $container)
    {
        return $container->getDefinition('twig.loader.filesystem');
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    protected function getBundleHierarchy(ContainerBuilder $container)
    {
        $bundleHierarchy = [];

        foreach ($container->getParameter('kernel.bundles_metadata') as $name => $bundle) {
            if (!array_key_exists($name, $bundleHierarchy)) {
                $bundleHierarchy[$name] = [
                    'metadata' => $bundle,
                    'paths'    => [],
                ];
            }

            if (is_dir($dir = $container->getParameter('kernel.root_dir') . '/Resources/' . $name . '/views')) {
                $bundleHierarchy[$name]['paths'][] = $dir;
            }
            $container->addResource(new FileExistenceResource($dir));

            if (is_dir($dir = $bundle['path'] . '/Resources/views')) {
                $bundleHierarchy[$name]['paths'][] = $dir;
            }
            $container->addResource(new FileExistenceResource($dir));

            $bundleHierarchy[$name]['template_dir'] = $bundle['path'] . '/Resources/templates';
        }

        return $bundleHierarchy;
    }
}
