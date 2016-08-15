<?php

namespace EdgarEz\SiteBuilderBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class SiteGenerator
 *
 * @package EdgarEz\SiteBuilderBundle\Generator
 */
class SiteGenerator extends Generator
{
    /**
     * @var Filesystem $filesystem
     */
    private $filesystem;

    /**
     * @var Kernel $kernel
     */
    private $kernel;

    /**
     * SiteGenerator constructor.
     *
     * @param Filesystem $filesystem
     * @param Kernel     $kernel
     */
    public function __construct(Filesystem $filesystem, Kernel $kernel)
    {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    /**
     * Generate Customer Bundle
     *
     * @param int $siteLocationID customer site content location ID
     * @param string $vendorName project vendor name
     * @param string $siteName customer site name
     * @param string $targetDir filesystem directory where bundle would be generated
     */
    public function generate($siteLocationID, $mediaSiteLocationID, $vendorName, $customerName, $modelName, $siteName, $excludeUriPrefixes, $targetDir)
    {
        $namespace = $vendorName . '\\' . ProjectGenerator::CUSTOMERS . '\\' . $customerName . '\\' . CustomerGenerator::SITES . '\\' . $siteName . 'Bundle';

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

        $basename = substr(ProjectGenerator::CUSTOMERS . $customerName . CustomerGenerator::SITES . $siteName . 'Bundle', 0, -6);
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => $siteName . 'Bundle',
            'format'    => 'yml',
            'bundle_basename' => $vendorName . $basename,
            'extension_alias' => Container::underscore($basename),
            'vendor_name' => $vendorName,
            'customer_name' => $customerName,
            'model_name' => $modelName,
            'site_name' => $siteName,
            'siteLocationID' => $siteLocationID,
            'mediaSiteLocationID' => $mediaSiteLocationID,
            'parent_model_bundle' => substr($vendorName . ProjectGenerator::MODELS . $modelName . 'Bundle', 0, -6),
            'siteaccess_model' => Container::underscore($vendorName . $modelName),
            'siteaccess' => Container::underscore($vendorName . $customerName . $siteName),
            'host' => 'ezplatform.lxc',
            'exclude_uri_prefixes' => $excludeUriPrefixes
        );

        $this->setSkeletonDirs(array($this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/skeleton')));
        $this->renderFile('site/Bundle.php.twig', $dir . '/' . $vendorName . $basename . 'Bundle.php', $parameters);
        $this->renderFile('site/Extension.php.twig', $dir . '/DependencyInjection/' . $vendorName . $basename . 'Extension.php', $parameters);
        $this->renderFile('site/Configuration.php.twig', $dir . '/DependencyInjection/Configuration.php', $parameters);
        $this->renderFile('site/Resources/config/ezplatform.yml.twig', $targetDir . '/' . $vendorName . '/ProjectBundle/Resources/config/sites/' . $parameters['siteaccess'] . '/ezplatform.yml', $parameters);

        $this->filesystem->mkdir($dir . '/Resources/public');
        $this->filesystem->mkdir($dir . '/Resources/public/css');
        $this->filesystem->mkdir($dir . '/Resources/public/js');
    }
}
