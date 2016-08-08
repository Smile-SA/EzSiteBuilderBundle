<?php

namespace EdgarEz\SiteBuilderBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Generates a Model bundle.
 */
class CustomerGenerator extends Generator
{
    private $filesystem;
    private $kernel;

    public function __construct(Filesystem $filesystem, Kernel $kernel)
    {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    public function generate($vendorName, $modelName, $modelLocationID, $excludeUriPrefixes, $targetDir)
    {
    }
}
