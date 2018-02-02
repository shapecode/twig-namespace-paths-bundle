<?php

namespace Shapecode\Bundle\TwigNamespacePathsBundle\Locator;

use Symfony\Component\Config\FileLocatorInterface;

/**
 * Class ChainFileLocator
 *
 * @package Shapecode\Bundle\TwigNamespacePathsBundle\Locator
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class ChainFileLocator implements FileLocatorInterface
{

    /** @var FileLocatorInterface[] */
    protected $locators;

    /**
     * @param FileLocatorInterface $fileLocator
     */
    public function addFileLocator(FileLocatorInterface $fileLocator)
    {
        $this->locators[] = $fileLocator;
    }

    /**
     * @inheritDoc
     */
    public function locate($name, $currentPath = null, $first = true)
    {
        $files = [];

        foreach ($this->locators as $locator) {
            try {
                $locatorFiles = $locator->locate($name, $currentPath, $first);

                if (!is_array($locatorFiles)) {
                    $locatorFiles = [$locatorFiles];
                }

                $files = array_merge($files, $locatorFiles);

            } catch (\Exception $exception) {

            }
        }

        if (empty($files)) {
            throw new \InvalidArgumentException();
        }

        $files = array_unique($files);

        if (count($files) > 0) {
            return $first ? $files[0] : $files;
        }

        return $files;
    }

}
