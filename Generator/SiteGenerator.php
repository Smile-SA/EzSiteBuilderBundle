<?php

namespace EdgarEz\SiteBuilderBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
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
     * Generate site bundle
     *
     * @param int $siteLocationID site content root location ID
     * @param int $mediaSiteLocationID site media root location ID
     * @param string $vendorName vendir name
     * @param string $customerName customer name
     * @param string $modelName model name
     * @param string $siteName site name
     * @param string $excludeUriPrefixes path prefix
     * @param string $host siteaccess host
     * @param boolean $mapuri siteaccess mapuri option
     * @param string $siteaccessSuffix siteaccess suffix
     * @param string $targetDir bundle target dir
     */
    public function generate(
        $sites,
        $siteLocationID,
        $mediaSiteLocationID,
        $vendorName,
        $customerName,
        $modelName,
        $excludeUriPrefixes,
        $targetDir
    ) {
        $site = current($sites);
        $namespace = $vendorName . '\\' . ProjectGenerator::CUSTOMERS . '\\' . $customerName . '\\' .
            CustomerGenerator::SITES . '\\' . $site['name'] . 'Bundle';

        $dir = $targetDir . '/' . strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(
                    sprintf(
                        'Unable to generate the bundle as the target directory "%s" exists but is a file.',
                        realpath($dir)
                    )
                );
            }
            $files = scandir($dir);
            if ($files != array('.', '..')) {
                throw new \RuntimeException(
                    sprintf(
                        'Unable to generate the bundle as the target directory "%s" is not empty.',
                        realpath($dir)
                    )
                );
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(
                    sprintf(
                        'Unable to generate the bundle as the target directory "%s" is not writable.',
                        realpath($dir)
                    )
                );
            }
        }

        $siteaccess = array();
        foreach ($sites as $languageCode => $newSite) {
            $sites[$languageCode]['siteaccess'] = strtolower($vendorName . '_' . $customerName . '_' . $newSite['name']);
            $sites[$languageCode]['exclude_uri_prefixes'] = $excludeUriPrefixes[$languageCode];
            $siteaccess[] = $sites[$languageCode]['siteaccess'];
        }
        $siteaccess = implode(', ', $siteaccess);

        $basename = ProjectGenerator::CUSTOMERS . $customerName . CustomerGenerator::SITES . $site['name'];
        $basenameUnderscore = ProjectGenerator::CUSTOMERS . '_' .
            $customerName . '_' . CustomerGenerator::SITES . '_' . $site['name'];
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => $site['name'] . 'Bundle',
            'format'    => 'yml',
            'bundle_basename' => $vendorName . $basename,
            'extension_alias' => strtolower($basenameUnderscore),
            'vendor_name' => $vendorName,
            'customer_name' => $customerName,
            'model_name' => $modelName,
            'siteLocationID' => $siteLocationID,
            'mediaSiteLocationID' => $mediaSiteLocationID,
            'parent_model_bundle' => $vendorName . ProjectGenerator::MODELS . $modelName,
            'siteaccess_model' => strtolower($vendorName . '_' . $modelName),
            'customer' => strtolower($customerName),
            'sites' => $sites,
            'siteaccess' => $siteaccess
        );

        $this->setSkeletonDirs(array($this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/skeleton')));
        $this->renderFile(
            'site/Bundle.php.twig',
            $dir . '/' . $vendorName . $basename . 'Bundle.php',
            $parameters
        );
        $this->renderFile(
            'site/Extension.php.twig',
            $dir . '/DependencyInjection/' . $vendorName . $basename . 'Extension.php',
            $parameters
        );
        $this->renderFile(
            'site/Configuration.php.twig',
            $dir . '/DependencyInjection/Configuration.php',
            $parameters
        );

        $firstSiteaccess = $siteaccess;
        $arraySiteaccess = explode(',', $siteaccess);
        if (is_array($arraySiteaccess)) {
            $firstSiteaccess = trim($arraySiteaccess[0]);
        }
        $this->renderFile(
            'site/Resources/config/ezplatform.yml.twig',
            $targetDir . '/' . $vendorName . '/ProjectBundle/Resources/config/sites/' .
            $firstSiteaccess . '/ezplatform.yml',
            $parameters
        );

        $this->filesystem->mkdir($dir . '/Resources/public');
        $this->filesystem->mkdir($dir . '/Resources/public/css');
        $this->filesystem->mkdir($dir . '/Resources/public/js');
    }
}
