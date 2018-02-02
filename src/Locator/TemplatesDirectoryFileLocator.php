<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle\Locator;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateReference as FrameworkTemplateReference;

/**
 * Class TemplatesDirectoryFileLocator
 *
 * @package Shapecode\Bundle\TwigNamespacePathsBundle\Locator
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class TemplatesDirectoryFileLocator implements FileLocatorInterface
{

    /** @var KernelInterface */
    protected $kernel;
    protected $pathPatterns;

    /**
     * @param KernelInterface $kernel A KernelInterface instance
     * @param string|null     $path   Path
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        $this->pathPatterns = [
            'bundle_resource' => [
                '%bundle_path%/Resources/templates/%template_bundle_name%/%template%',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function locate($name, $dir = null, $first = true)
    {
        $templateName = null;

        if ($name instanceof FrameworkTemplateReference) {
            $templateName = $name->getPath();
            $templateName = str_replace('Bundle/Resources/views', '', $templateName);
        } elseif ($name instanceof TemplateReference) {
            $templateName = $name->getLogicalName();
        } elseif (is_array($name)) {
            $templateName = $name[0];
        } else {
            $templateName = $name;
        }

        if ('@' === $templateName[0]) {
            return $this->locateBundleResource($name, $dir, $first);
        }

        throw new \InvalidArgumentException();
    }

    /**
     * Locate Resource Theme aware. Only working for bundle resources!
     *
     * Method inlined from Symfony\Component\Http\Kernel
     *
     * @param string $name
     * @param string $dir
     * @param bool   $first
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function locateBundleResource($name, $dir = null, $first = true)
    {
        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }

        $bundleName = substr($name, 1);
        $path = '';
        if (false !== strpos($bundleName, '/')) {
            list($bundleName, $path) = explode('/', $bundleName, 2);
        }

        if (!preg_match('/(Bundle)$/i', $bundleName)) {
            $bundleName .= 'Bundle';
            if (0 !== strpos($path, 'Resources')) {
                $path = 'Resources/views/' . $path;
            }
        }

        if (0 !== strpos($path, 'Resources')) {
            throw new \RuntimeException('Template files have to be in Resources.');
        }

        $bundles = $this->kernel->getBundles();
        $files = [];

        $parameters = [
            '%dir%'                  => $dir,
            '%override_path%'        => substr($path, strlen('Resources/')),
            '%template%'             => substr($path, strlen('Resources/views/')),
            '%template_bundle_name%' => $bundleName,
        ];

        foreach ($bundles as $bundle) {
            $parameters = array_merge($parameters, [
                '%bundle_path%' => $bundle->getPath(),
                '%bundle_name%' => $bundle->getName(),
            ]);

            $checkPaths = $this->getPathsForBundleResource($parameters);

            foreach ($checkPaths as $checkPath) {
                if (file_exists($checkPath)) {
                    if ($first) {
                        return $checkPath;
                    }
                    $files[] = $checkPath;
                }
            }
        }

        if (count($files) > 0) {
            return $first ? $files[0] : $files;
        }

        throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }

    protected function getPathsForBundleResource($parameters)
    {
        $pathPatterns = [];
        $paths = [];

        $pathPatterns = array_merge($pathPatterns, $this->pathPatterns['bundle_resource']);

        foreach ($pathPatterns as $pattern) {
            $paths[] = strtr($pattern, $parameters);
        }

        return $paths;
    }
}
