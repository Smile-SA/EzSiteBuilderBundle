<?php

namespace Smile\EzSiteBuilderBundle\Service\Task;

use Smile\EzSiteBuilderBundle\Command\Validators;
use Smile\EzSiteBuilderBundle\Generator\ModelGenerator;
use Smile\EzSiteBuilderBundle\Generator\ProjectGenerator;
use Smile\EzSiteBuilderBundle\Service\ModelService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\FieldType\Checkbox\Value;
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

    /** @var LocationService $locationService */
    protected $locationService;

    /** @var ContentService $contentService */
    protected $contentService;

    /** @var LanguageService $languageService */
    protected $languageService;

    /** @var string $kernelRootDir */
    protected $kernelRootDir;

    public function __construct(
        Filesystem $filesystem,
        Kernel $kernel,
        ModelService $modelService,
        LocationService $locationService,
        ContentService $contentService,
        LanguageService $languageService,
        $kernelRootDir
    ) {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->modelService = $modelService;
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->languageService = $languageService;
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

    public function validateActivateParameters($parameters)
    {
        try {
            Validators::validateLocationID($parameters['modelID']);
        } catch (InvalidArgumentException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function execute($command, array $parameters, Container $container, $userID)
    {
        switch ($command) {
            case 'generate':
                try {
                    $this->validateParameters($parameters);

                    $basename = ProjectGenerator::MAIN;
                    $extensionAlias = 'smileez_sb.' . strtolower($basename);
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

                    $languageCode = $this->languageService->getDefaultLanguageCode();

                    $modelsLocationID = $container->getParameter(
                        'smileez_sb.' . strtolower($basename) . '.default.models_location_id'
                    );
                    $returnValue = $this->modelService->createModelContent(
                        $modelsLocationID,
                        $parameters['modelName'],
                        $languageCode
                    );
                    $excludeUriPrefixes = $returnValue['excludeUriPrefixes'];
                    $modelLocationID = $returnValue['modelLocationID'];

                    $mediaModelsLocationID = $container->getParameter(
                        'smileez_sb.' . strtolower($basename) . '.default.media_models_location_id'
                    );
                    $returnValue = $this->modelService->createMediaModelContent(
                        $mediaModelsLocationID,
                        $parameters['modelName'],
                        $languageCode
                    );
                    $mediaModelLocationID = $returnValue['mediaModelLocationID'];

                    $this->modelService->updateGlobalRole($modelLocationID, $mediaModelLocationID);

                    /** @var ModelGenerator $generator */
                    $generator = new ModelGenerator(
                        $this->filesystem,
                        $this->kernel
                    );
                    $generator->generate(
                        $languageCode,
                        $vendorName,
                        $parameters['modelName'],
                        $modelLocationID,
                        $mediaModelLocationID,
                        $excludeUriPrefixes,
                        $container->getParameter('smile_ez_site_builder.host'),
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

                    $basename = ProjectGenerator::MAIN;
                    $extensionAlias = 'smileez_sb.' . strtolower($basename);
                    $vendorName = $container->getParameter($extensionAlias . '.default.vendor_name');

                    $customers = $container->getParameter('smile_ez_site_builder.customer');
                    $this->modelService->addSiteaccessLimitation(
                        strtolower($vendorName . '_' . $parameters['modelName']),
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
            case 'activate':
                try {
                    $this->validateActivateParameters($parameters);

                    $model = $this->locationService->loadLocation($parameters['modelID']);

                    $contentInfo = $model->getContentInfo();
                    $contentDraft = $this->contentService->createContentDraft($contentInfo);
                    $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
                    $contentUpdateStruct->initialLanguageCode = $contentInfo->mainLanguageCode;
                    $contentUpdateStruct->setField('activated', new Value(true));
                    $contentDraft = $this->contentService->updateContent(
                        $contentDraft->versionInfo,
                        $contentUpdateStruct
                    );
                    $this->contentService->publishVersion($contentDraft->versionInfo);
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
