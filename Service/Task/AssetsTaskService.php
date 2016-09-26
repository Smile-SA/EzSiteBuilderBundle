<?php

namespace Smile\EzSiteBuilderBundle\Service\Task;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use Smile\EzSiteBuilderBundle\Command\Validators;
use Smile\EzSiteBuilderBundle\Generator\CustomerGenerator;
use Smile\EzSiteBuilderBundle\Generator\ProjectGenerator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AssetsTaskService
 *
 * @package Smile\EzSiteBuilderBundle\Service\Task
 */
class AssetsTaskService extends BaseTaskService implements TaskInterface
{
    /** @var Kernel $kernel */
    protected $kernel;

    /** @var Filesystem $filesystem */
    protected $filesystem;

    public function __construct(
        Kernel $kernel,
        Filesystem $filesystem
    ) {
        $this->kernel = $kernel;
        $this->filesystem = $filesystem;

        $this->message = false;
    }

    public function validateParameters($parameters)
    {
        try {
        } catch (InvalidArgumentException $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Execute task
     *
     * @param string $command
     * @param array $parameters
     * @return bool
     */
    public function execute($command, array $parameters, Container $container, $userID)
    {
        switch ($command) {
            case 'install':
                try {
                    $this->validateParameters($parameters);

                    $bundlesDir = 'web/bundles/';
                    $this->filesystem->mkdir($bundlesDir, 0777);

                    $bundlePathName = $parameters['bundlePath'] . '\\' . $parameters['bundleName'];
                    /** @var Bundle $bundle */
                    $bundle = new $bundlePathName();

                    $originDir = $bundle->getPath() . '/Resources/public';
                    $targetDir = $bundlesDir . preg_replace('/bundle$/', '', strtolower($bundle->getName()));

                    $this->filesystem->symlink($originDir, $targetDir);
                } catch (IOException $e) {
                    $this->message = $e->getMessage();
                    return false;
                } catch (\RuntimeException $e) {
                    $this->message = $e->getMessage();
                    return false;
                } catch (\Exception $e) {
                    $this->message = $e->getMessage();
                    return false;
                }
                break;
            default:
                break;
        }

        return true;
    }
}
