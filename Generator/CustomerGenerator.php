<?php

namespace EdgarEz\SiteBuilderBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class CustomerGenerator
 *
 * @package EdgarEz\SiteBuilderBundle\Generator
 */
class CustomerGenerator extends Generator
{
    const BUNDLE = 'SitesBundle';
    const SITES = 'Sites';

    /**
     * @var Filesystem $filesystem
     */
    private $filesystem;

    /**
     * @var Kernel $kernel
     */
    private $kernel;

    /**
     * CustomerGenerator constructor.
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
     * @param int $customerLocationID customer content location ID
     * @param string $vendorName project vendor name
     * @param string $targetDir filesystem directory where bundle would be generated
     */
    public function generate($customerLocationID, $vendorName, $customerName, $targetDir)
    {
        $namespace = $vendorName . '\\' . $customerName . '\\' . self::BUNDLE;

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

        $basename = substr($vendorName . $customerName . self::BUNDLE, 0, -6);
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => self::BUNDLE,
            'format'    => 'yml',
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
            'settings' => array(
                'customerLocationID' => $customerLocationID
            )
        );

        $this->setSkeletonDirs(array($this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/skeleton')));
        $this->renderFile('customer/Bundle.php.twig', $dir . '/' . $basename . 'Bundle.php', $parameters);
        $this->renderFile('customer/Extension.php.twig', $dir . '/DependencyInjection/' . $basename . 'Extension.php', $parameters);
        $this->renderFile('customer/Configuration.php.twig', $dir . '/DependencyInjection/Configuration.php', $parameters);
        $this->renderFile('customer/default_settings.yml.twig', $dir . '/Resources/config/default_settings.yml', $parameters);

        $this->filesystem->mkdir($targetDir . '/' . $vendorName . '/' . $customerName . '/' . self::SITES);
    }
}
