<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle\Liip;

use Liip\ThemeBundle\Twig\Loader\FilesystemLoader as BaseFilesystemLoader;

/**
 * Class FilesystemLoader
 *
 * @package Shapecode\Bundle\TwigNamespacePathsBundle\Liip
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class FilesystemLoader extends BaseFilesystemLoader
{

    /**
     * @inheritdoc
     */
    protected function findTemplate($template, $throw = true)
    {
        $logicalName = (string)$template;

        if ($this->activeTheme) {
            $logicalName .= '|' . $this->activeTheme->getName();
        }

        if (isset($this->cache[$logicalName])) {
            return $this->cache[$logicalName];
        }

        $file = null;
        $previous = null;

        $templateReference = $this->parser->parse($template);

        try {
            $file = $this->locator->locate($templateReference);
        } catch (\Exception $e) {
            $previous = $e;

            // for BC
            try {

                $fileName = str_replace('views/', '', $templateReference->getPath());
                $fileName = str_replace('Bundle/Resources/', '/', $fileName);
                $fileName = str_replace('Resources/', '', $fileName);

                $file = parent::findTemplate($fileName);
            } catch (\Twig_Error_Loader $e) {
                $previous = $e;
            }
        }

        if (false === $file || null === $file) {
            if ($throw) {
                throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $logicalName), -1, null, $previous);
            }

            return false;
        }

        return $this->cache[$logicalName] = $file;
    }
}
