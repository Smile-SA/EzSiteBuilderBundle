<?php

namespace EdgarEz\SiteBuilderBundle\Generator;


use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class ProjectGenerator
 *
 * @package EdgarEz\SiteBuilderBundle\Generator
 */
class ProjectGenerator extends Generator
{
    const MAIN = 'Project';
    const BUNDLE = 'ProjectBundle';
    const MODELS = 'Models';
    const CUSTOMERS = 'Customers';

    /**
     * @var Filesystem $filesystem
     */
    private $filesystem;

    /**
     * @var Kernel $kernel
     */
    private $kernel;

    /**
     * ProjectGenerator constructor.
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
     * Generate project bundle
     *
     * @param int $modelsLocationID models content root location ID
     * @param int $customersLocationID customers content root location ID
     * @param int $mediaModelsLocationID models media root location ID
     * @param int $mediaCustomersLocationID customers media root location ID
     * @param int $userCreatorsLocationID user creator groups root location ID
     * @param int $userEditorsLocationID user editor groups root location ID
     * @param string $vendorName vendor name
     * @param string $targetDir bundle target dir
     */
    public function generate(
        $modelsLocationID,
        $customersLocationID,
        $mediaModelsLocationID,
        $mediaCustomersLocationID,
        $userCreatorsLocationID,
        $userEditorsLocationID,
        $vendorName,
        $targetDir
    )
    {
        $namespace = $vendorName . '\\' . self::BUNDLE;

        $dir = rtrim($targetDir, '/') . '/' . strtr($namespace, '\\', '/');
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

        $basename = substr(self::BUNDLE, 0, -6);
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => self::BUNDLE,
            'format'    => 'yml',
            'bundle_basename' => $vendorName . $basename,
            'extension_alias' => Container::underscore($basename),
            'settings' => array(
                'vendor_name' => $vendorName,
                'dir' => $targetDir,
                'modelsLocationID' => $modelsLocationID,
                'customersLocationID' => $customersLocationID,
                'mediaModelsLocationID' => $mediaModelsLocationID,
                'mediaCustomersLocationID' => $mediaCustomersLocationID,
                'userCreatorsLocationID' => $userCreatorsLocationID,
                'userEditorsLocationID' => $userEditorsLocationID,
                'namespace' => $namespace
            )
        );

        $this->setSkeletonDirs(array($this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/skeleton')));
        $this->renderFile('project/Bundle.php.twig', $dir . '/' . $vendorName . $basename . 'Bundle.php', $parameters);
        $this->renderFile('project/Extension.php.twig', $dir . '/DependencyInjection/' . $vendorName . $basename . 'Extension.php', $parameters);
        $this->renderFile('project/Configuration.php.twig', $dir . '/DependencyInjection/Configuration.php', $parameters);
        $this->renderFile('project/Resources/config/default_settings.yml.twig', $dir . '/Resources/config/default_settings.yml', $parameters);

        $this->filesystem->mkdir($targetDir . '/' . $vendorName . '/' . self::MODELS);
        $this->filesystem->mkdir($targetDir . '/' . $vendorName . '/' . self::CUSTOMERS);
    }
}
