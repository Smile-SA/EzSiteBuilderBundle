<?php

namespace EdgarEz\SiteBuilderBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Generates a Model bundle.
 */
class ModelGenerator extends Generator
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
        $namespace = $vendorName . '\\' . ProjectGenerator::PROJECT . '\\' . ProjectGenerator::MODELS . '\\' . $modelName . 'Bundle';

        $dir = $targetDir . '/' . strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" exists but is a file.', realpath($dir)));
            }
            $files = scandir($dir);
            if ($files != array('.', '..')) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not empty.', realpath($dir)));
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not writable.', realpath($dir)));
            }
        }

        $basename = substr($vendorName . ProjectGenerator::PROJECT . ProjectGenerator::MODELS . $modelName . 'Bundle', 0, -6);
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => $modelName . 'Bundle',
            'format'    => 'yml',
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
            'vendor_name' => $vendorName,
            'model_name' => $modelName,
            'modelLocationID' => $modelLocationID,
            'siteaccess' => Container::underscore($vendorName . $modelName),
            'host' => 'ezplatform.lxc',
            'exclude_uri_prefixes' => $excludeUriPrefixes
        );

        $this->setSkeletonDirs(array($this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/skeleton')));
        $this->renderFile('model/Bundle.php.twig', $dir . '/' . $basename . 'Bundle.php', $parameters);
        $this->renderFile('model/Extension.php.twig', $dir . '/DependencyInjection/' . $basename . 'Extension.php', $parameters);
        $this->renderFile('model/Configuration.php.twig', $dir . '/DependencyInjection/Configuration.php', $parameters);
        $this->renderFile('model/Resources/config/ezplatform.yml.twig', $dir . '/Resources/config/ezplatform.yml', $parameters);
        $this->renderFile('model/Resources/views/pagelayout.html.twig.twig', $dir . '/Resources/views/pagelayout.html.twig', $parameters);
        $this->renderFile('model/Resources/views/full/model.html.twig.twig', $dir . '/Resources/views/full/model.html.twig', $parameters);
    }
}
