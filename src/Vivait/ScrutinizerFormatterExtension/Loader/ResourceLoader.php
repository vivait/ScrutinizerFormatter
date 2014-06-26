<?php

namespace Vivait\ScrutinizerFormatterExtension\Loader;

use PhpSpec\Loader\Suite;
use PhpSpec\Locator\ResourceManager;

class ResourceLoader extends \PhpSpec\Loader\ResourceLoader {

    /**
     * @var \PhpSpec\Locator\ResourceManager
     */
    private $manager;

    /**
     * @param ResourceManager $manager
     */
    public function __construct(ResourceManager $manager)
    {
        parent::__construct($manager);
        $this->manager = $manager;
    }

    /**
     * @param string       $locator
     * @param integer|null $line
     *
     * @return Suite
     */
    public function load($locator, $line = null)
    {
        foreach ($this->manager->locateResources($locator) as $resource) {
            if ($resource->getSpecFilename() == $locator){
                return parent::load($locator, $line);
            }
        }

        return new Suite;
    }
}