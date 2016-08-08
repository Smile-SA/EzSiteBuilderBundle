<?php
/**
 * Created by PhpStorm.
 * User: Emmanuel
 * Date: 07/08/2016
 * Time: 13:12
 */

namespace EdgarEz\SiteBuilderBundle\Generator;


use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class ProjectGenerator extends Generator
{
    const MAIN = 'Project';
    const BUNDLE = 'ProjectBundle';
    const PROJECT = 'SiteBuilder';
    const MODELS = 'Models';

    private $filesystem;
    private $kernel;

    public function __construct(Filesystem $filesystem, Kernel $kernel)
    {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    public function generate(
        $modelsLocationID,
        $customersLocationID,
        $userCreatorsLocationID,
        $userEditorsLocationID,
        $vendorName,
        $targetDir
    )
    {
        $namespace = $vendorName . '\\' . self::PROJECT . '\\' . self::BUNDLE;

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

        $basename = substr($vendorName . self::PROJECT . self::BUNDLE, 0, -6);
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => self::BUNDLE,
            'format'    => 'yml',
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
            'settings' => array(
                'modelsLocationID' => $modelsLocationID,
                'customersLocationID' => $customersLocationID,
                'userCreatorsLocationID' => $userCreatorsLocationID,
                'userEditorsLocationID' => $userEditorsLocationID,
                'namespace' => $namespace
            )
        );

        $this->setSkeletonDirs(array($this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/skeleton')));
        $this->renderFile('project/Bundle.php.twig', $dir . '/' . $basename . 'Bundle.php', $parameters);
        $this->renderFile('project/Extension.php.twig', $dir . '/DependencyInjection/' . $basename . 'Extension.php', $parameters);
        $this->renderFile('project/Configuration.php.twig', $dir . '/DependencyInjection/Configuration.php', $parameters);
        $this->renderFile('project/default_settings.yml.twig', $dir . '/Resources/config/default_settings.yml', $parameters);

        $this->filesystem->mkdir($targetDir . '/' . $vendorName . '/' . self::PROJECT . '/' . self::MODELS);
    }
}
