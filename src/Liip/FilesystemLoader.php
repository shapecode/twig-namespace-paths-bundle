<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle\Liip;

use Liip\ThemeBundle\ActiveTheme;
use Liip\ThemeBundle\Twig\Loader\FilesystemLoader as BaseFilesystemLoader;
use Symfony\Component\Templating\TemplateReferenceInterface;

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
     * @var ActiveTheme|null
     */
    protected $activeTheme;

    /**
     * Define the active theme
     *
     * @param ActiveTheme $activeTheme
     */
    public function setActiveTheme(ActiveTheme $activeTheme = null)
    {
        $this->activeTheme = $activeTheme;
    }

    /**
     * Returns the path to the template file.
     *
     * The file locator is used to locate the template when the naming convention
     * is the symfony one (i.e. the name can be parsed).
     * Otherwise the template is located using the locator from the twig library.
     *
     * @param string|TemplateReferenceInterface $template The template
     * @param bool                              $throw    When true, a \Twig_Error_Loader exception will be thrown if a template could not be found
     *
     * @return string The path to the template file
     *
     * @throws \Twig_Error_Loader if the template could not be found
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
