<?php

namespace EdgarEz\SiteBuilderBundle\Service\Task;

use EdgarEz\SiteBuilderBundle\Command\Validators;
use EdgarEz\SiteBuilderBundle\Generator\ModelGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Service\ModelService;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class ModelTaskService extends BaseTaskService implements TaskInterface
{
    /** @var ModelService $customerService */
    protected $modelService;

    /** @var Filesystem $filesystem */
    protected $filesystem;

    /** @var Kernel $kernel */
    protected $kernel;

    /** @var string $kernelRootDir */
    protected $kernelRootDir;

    public function __construct(
        Filesystem $filesystem,
        Kernel $kernel,
        ModelService $modelService,
        $kernelRootDir
    ) {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->modelService = $modelService;
        $this->kernelRootDir = $kernelRootDir;

        $this->message = false;
    }


    public function validateParameters($parameters)
    {
        try {
            Validators::validateModelName($parameters['modelName']);
        } catch (InvalidArgumentException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function execute($command, array $parameters, Container $container)
    {
        switch ($command) {
            case 'generate':
                try {
                    $this->validateParameters($parameters);

                    $basename = substr(ProjectGenerator::BUNDLE, 0, -6);
                    $extensionAlias = 'edgarez_sb.' . Container::underscore($basename);
                    $vendorName = $container->getParameter($extensionAlias . '.default.vendor_name');

                    $exists = $this->modelService->exists(
                        $parameters['modelName'],
                        $vendorName,
                        $this->kernelRootDir . '/../src'
                    );
                    if ($exists) {
                        $this->message = 'Model already exists with this name';
                        return false;
                    }

                    $basename = ProjectGenerator::MAIN ;

                    $modelsLocationID = $container->getParameter(
                        'edgarez_sb.' . Container::underscore($basename) . '.default.models_location_id'
                    );
                    $returnValue = $this->modelService->createModelContent(
                        $modelsLocationID,
                        $parameters['modelName']
                    );
                    $excludeUriPrefixes = $returnValue['excludeUriPrefixes'];
                    $modelLocationID = $returnValue['modelLocationID'];

                    $mediaModelsLocationID = $container->getParameter(
                        'edgarez_sb.' . Container::underscore($basename) . '.default.media_models_location_id'
                    );
                    $returnValue = $this->modelService->createMediaModelContent(
                        $mediaModelsLocationID,
                        $parameters['modelName']
                    );
                    $mediaModelLocationID = $returnValue['mediaModelLocationID'];

                    $this->modelService->updateGlobalRole($modelLocationID, $mediaModelLocationID);

                    /** @var ModelGenerator $generator */
                    $generator = new ModelGenerator(
                        $this->filesystem,
                        $this->kernel
                    );
                    $generator->generate(
                        $vendorName,
                        $parameters['modelName'],
                        $modelLocationID,
                        $mediaModelLocationID,
                        $excludeUriPrefixes,
                        $container->getParameter('edgar_ez_site_builder.host'),
                        $this->kernelRootDir . '/../src'
                    );

                    $namespace = $vendorName . '\\' . ProjectGenerator::MODELS .
                        '\\' . $parameters['modelName'] . 'Bundle';
                    $bundle = $vendorName . ProjectGenerator::MODELS . $parameters['modelName'] . 'Bundle';
                    $this->updateKernel($this->kernel, $namespace, $bundle);
                } catch (\RuntimeException $e) {
                    $this->message = $e->getMessage();
                    return false;
                } catch (\Exception $e) {
                    $this->message = $e->getMessage();
                    return false;
                }
                break;
            case 'policy':
                try {
                    $this->validateParameters($parameters);

                    $basename = substr(ProjectGenerator::BUNDLE, 0, -6);
                    $extensionAlias = 'edgarez_sb.' . Container::underscore($basename);
                    $vendorName = $container->getParameter($extensionAlias . '.default.vendor_name');

                    $customers = $container->getParameter('edgar_ez_site_builder.customer');
                    $this->modelService->addSiteaccessLimitation(
                        Container::underscore($vendorName . $parameters['modelName']),
                        $customers
                    );
                } catch (\RuntimeException $e) {
                    $this->message = $e->getMessage();
                    return false;
                } catch (\Exception $e) {
                    $this->message = $e->getMessage();
                    return false;
                }
                break;
        }

        return true;
    }
}
