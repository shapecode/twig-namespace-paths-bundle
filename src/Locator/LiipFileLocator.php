<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Shapecode\Bundle\TwigNamespacePathsBundle\Locator;

use Symfony\Component\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateReference as FrameworkTemplateReference;

/**
 * Class LiipFileLocator
 *
 * @package Shapecode\Bundle\TwigNamespacePathsBundle\Locator
 * @author  Nikita Loges
 */
class LiipFileLocator extends \Liip\ThemeBundle\Locator\FileLocator
{
    /**
     * @inheritDoc
     */
    public function locate($name, $dir = null, $first = true)
    {
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

        return parent::locate($templateName, $dir, $first);
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

        $bundles = $this->kernel->getBundle($bundleName, false, true);

        // Symfony 4+ no longer supports inheritance and so we only get a single bundle
        if (!is_array($bundles)) {
            $bundles = [$bundles];
        }

        $files = [];

        $parameters = [
            '%app_path%'       => $this->path,
            '%dir%'            => $dir,
            '%override_path%'  => substr($path, strlen('Resources/')),
            '%current_theme%'  => $this->lastTheme,
            '%current_device%' => $this->activeTheme->getDeviceType(),
            '%template%'       => substr($path, strlen('Resources/views/')),
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
}
