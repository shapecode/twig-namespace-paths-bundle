<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class TwigNamespaceCompiler
 *
 * @package Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class TwigNamespaceCompiler implements CompilerPassInterface
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $twigFilesystemLoaderDefinition = $this->getTwigFileSystemLoader($container);
        $bundleHierarchy = $this->getBundleHierarchy($container);

        $additionals = [
            'Direct',
        ];

        // supports it already
        if (Kernel::VERSION_ID < 30400) {
            $additionals[] = '!';
        }

        foreach ($bundleHierarchy as $name => $bundle) {
            $namespace = $this->normalizeBundleName($name);

            foreach ($bundle['paths'] as $path) {
                foreach ($additionals as $additional) {
                    $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$path, $additional . $namespace]);
                }
            }

            foreach ($bundleHierarchy as $sName => $sBundle) {
                $sNamespace = $this->normalizeBundleName($sName);

                $dir = $bundle['template_dir'] . '/' . $sName;

                if (is_dir($dir)) {
                    $twigFilesystemLoaderDefinition->addMethodCall('prependPath', [$dir, $sNamespace]);
                }
            }
        }
    }

    /**
     * @param $name
     *
     * @return string
     */
    private function normalizeBundleName($name)
    {
        if ('Bundle' === substr($name, -6)) {
            $name = substr($name, 0, -6);
        }

        return $name;
    }

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
