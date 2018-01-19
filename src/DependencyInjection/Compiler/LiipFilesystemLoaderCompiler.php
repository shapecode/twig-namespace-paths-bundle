<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler;

use Shapecode\Bundle\TwigNamespacePathsBundle\Liip\FilesystemLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class LiipFilesystemLoaderCompiler
 *
 * @package Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class LiipFilesystemLoaderCompiler implements CompilerPassInterface
{

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $liipClass = '\Liip\ThemeBundle\LiipThemeBundle';

        if (!class_exists($liipClass)) {
            return;
        }

        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.filesystem');
        $twigFilesystemLoaderDefinition->setClass(FilesystemLoader::class);

    }
}
