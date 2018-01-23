<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler;

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
class TwigNamespaceCompiler extends AbstractCompiler implements CompilerPassInterface
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
            $namespaceBundle = $namespace . 'Bundle';

            foreach ($bundle['paths'] as $path) {
                $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$path, $namespaceBundle]);

                foreach ($additionals as $additional) {
                    $toAdd = $additional . $namespace;
                    $toAdd2 = $additional . $namespaceBundle;

                    $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$path, $toAdd]);
                    $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$path, $toAdd2]);
                }
            }

            foreach ($bundleHierarchy as $sName => $sBundle) {
                $sNamespace = $this->normalizeBundleName($sName);
                $sNamespaceBundle = $sNamespace . 'Bundle';

                $dir = $bundle['template_dir'] . '/' . $sName;

                if (is_dir($dir)) {
                    $twigFilesystemLoaderDefinition->addMethodCall('prependPath', [$dir, $sNamespace]);
                    $twigFilesystemLoaderDefinition->addMethodCall('prependPath', [$dir, $sNamespaceBundle]);
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
}
