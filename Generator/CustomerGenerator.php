<?php

namespace EdgarEz\SiteBuilderBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;

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
     * Generate customer bundle
     *
     * @param int $customerLocationID customer content root location ID
     * @param int $mediaCustomerLocationID customer media root location ID
     * @param int $customerUserCreatorsGroupLocationID customer user groupe creator location ID
     * @param int $customerUserEditorsGroupLocationID customer user groupe editor location ID
     * @param int $customerRoleCreatorID customer creator role ID
     * @param int $customerRoleEditorID customer editor role ID
     * @param string $vendorName vendor name
     * @param string $customerName customer name
     * @param string $targetDir bundle target dir
     */
    public function generate(
        $customerLocationID,
        $mediaCustomerLocationID,
        $customerUserCreatorsGroupLocationID,
        $customerUserEditorsGroupLocationID,
        $customerRoleCreatorID,
        $customerRoleEditorID,
        $vendorName,
        $customerName,
        $targetDir
    )
    {
        $namespace = $vendorName . '\\' . ProjectGenerator::CUSTOMERS . '\\' . $customerName . '\\' . self::BUNDLE;

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

        $basename = substr(ProjectGenerator::CUSTOMERS . $customerName . self::BUNDLE, 0, -6);
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => self::BUNDLE,
            'format'    => 'yml',
            'bundle_basename' => $vendorName . $basename,
            'extension_alias' => Container::underscore($basename),
            'settings' => array(
                'customerLocationID' => $customerLocationID,
                'mediaCustomerLocationID' => $mediaCustomerLocationID,
                'customerUserCreatorsGroupLocationID' => $customerUserCreatorsGroupLocationID,
                'customerUserEditorsGroupLocationID' => $customerUserEditorsGroupLocationID,
                'customerRoleCreatorID' => $customerRoleCreatorID,
                'customerRoleEditorID' => $customerRoleEditorID
            )
        );

        $this->setSkeletonDirs(array($this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/skeleton')));
        $this->renderFile('customer/Bundle.php.twig', $dir . '/' . $vendorName . $basename . 'Bundle.php', $parameters);
        $this->renderFile('customer/Extension.php.twig', $dir . '/DependencyInjection/' . $vendorName . $basename . 'Extension.php', $parameters);
        $this->renderFile('customer/Configuration.php.twig', $dir . '/DependencyInjection/Configuration.php', $parameters);
        $this->renderFile('customer/default_settings.yml.twig', $dir . '/Resources/config/default_settings.yml', $parameters);

        $configFile = $targetDir . '/' . $vendorName . '/ProjectBundle/Resources/config/edgarezsb.yml';
        $edgarezYaml = Yaml::parse(file_get_contents($configFile));
        $customers = $edgarezYaml['parameters']['edgar_ez_site_builder.customer'];
        $customers[] = strtolower($customerName);
        $edgarezYaml['parameters']['edgar_ez_site_builder.customer'] = $customers;
        file_put_contents($configFile, Yaml::dump($edgarezYaml));

        $this->filesystem->mkdir($targetDir . '/' . $vendorName . '/' . ProjectGenerator::CUSTOMERS . '/' . $customerName . '/' . self::SITES);
    }
}
