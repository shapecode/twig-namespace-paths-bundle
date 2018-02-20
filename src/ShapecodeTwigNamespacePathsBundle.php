<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle;

use Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler\FileLocatorCompiler;
use Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler\FilesystemLoaderCompiler;
use Shapecode\Bundle\TwigNamespacePathsBundle\DependencyInjection\Compiler\TwigNamespaceCompiler;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ShapecodeTwigNamespacePathsBundle
 *
 * @package Shapecode\Bundle\TwigNamespacePathsBundle
 * @author  Nikita Loges
 */
class ShapecodeTwigNamespacePathsBundle extends Bundle
{

    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FileLocatorCompiler());
        $container->addCompilerPass(new TwigNamespaceCompiler());
        $container->addCompilerPass(new FilesystemLoaderCompiler(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -5);
    }
}
