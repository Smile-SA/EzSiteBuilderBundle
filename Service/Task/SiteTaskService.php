<?php

namespace EdgarEz\SiteBuilderBundle\Service\Task;

use EdgarEz\SiteBuilderBundle\Command\Validators;
use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Generator\SiteGenerator;
use EdgarEz\SiteBuilderBundle\Service\SiteService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\Core\FieldType\Checkbox\Value;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class SiteTaskService extends BaseTaskService implements TaskInterface
{
    /** @var SiteService $siteService */
    protected $siteService;

    /** @var RoleService $roleService */
    protected $roleService;

    /** @var Filesystem $filesystem */
    protected $filesystem;

    /** @var Kernel $kernel */
    protected $kernel;

    /** @var LocationService $locationService */
    protected $locationService;

    /** @var ContentService $contentService */
    protected $contentService;

    /** @var string $kernelRootDir */
    protected $kernelRootDir;

    public function __construct(
        Filesystem $filesystem,
        Kernel $kernel,
        LocationService $locationService,
        ContentService $contentService,
        SiteService $siteService,
        RoleService $roleService,
        $kernelRootDir
    ) {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->siteService = $siteService;
        $this->roleService = $roleService;
        $this->kernelRootDir = $kernelRootDir;

        $this->message = false;
    }

    public function validateParameters($parameters)
    {
        try {
            Validators::validateCustomerName($parameters['customerName']);
            Validators::validateLocationID($parameters['customerContentLocationID']);
            Validators::validateLocationID($parameters['customerMediaLocationID']);
            Validators::validateSiteName($parameters['siteName']);
            Validators::validateHost($parameters['host']);
            Validators::validateSiteaccessSuffix($parameters['suffix']);

            $model = explode('-', $parameters['model']);
            if (!is_array($model) || count($model) != 2) {
                throw new \Exception('Fail to identify model by content or media location ID');
            }
            Validators::validateLocationID($model[0]);
            Validators::validateLocationID($model[1]);

            $this->locationService->loadLocation($model[0]);
            $this->locationService->loadLocation($model[1]);
        } catch (InvalidArgumentException $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function validateActivateParameters($parameters)
    {
        try {
            Validators::validateLocationID($parameters['siteID']);

            $this->locationService->loadLocation($parameters['siteID']);
        } catch (InvalidArgumentException $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function execute($command, array $parameters, Container $container)
    {
        switch ($command) {
            case 'generate':
                try {
                    $this->validateParameters($parameters);

                    $basename = ProjectGenerator::MAIN;
                    $extensionAlias = 'edgarez_sb.' . strtolower($basename);
                    $vendorName = $container->getParameter($extensionAlias . '.default.vendor_name');

                    $exists = $this->siteService->exists(
                        $parameters['siteName'],
                        $parameters['customerName'],
                        $vendorName,
                        $this->kernelRootDir . '/../src'
                    );
                    if ($exists) {
                        $this->message = 'Site already exists with this name for this customer';
                        return false;
                    }

                    $model = explode('-', $parameters['model']);
                    $modelLocation = $this->locationService->loadLocation($model[0]);

                    $returnValue = $this->siteService->createSiteContent(
                        $parameters['customerContentLocationID'],
                        $model[0],
                        $parameters['siteName']
                    );
                    $siteLocationID = $returnValue['siteLocationID'];
                    $excludeUriPrefixes = $returnValue['excludeUriPrefixes'];

                    $returnValue = $this->siteService->createMediaSiteContent(
                        $parameters['customerMediaLocationID'],
                        $model[1],
                        $parameters['siteName']
                    );
                    $mediaSiteLocationID = $returnValue['mediaSiteLocationID'];

                    $generator = new SiteGenerator(
                        $this->filesystem,
                        $this->kernel
                    );
                    $generator->generate(
                        $siteLocationID,
                        $mediaSiteLocationID,
                        $vendorName,
                        $parameters['customerName'],
                        $modelLocation->contentInfo->name,
                        $parameters['siteName'],
                        $excludeUriPrefixes,
                        $parameters['host'],
                        $parameters['mapuri'],
                        $parameters['suffix'],
                        $this->kernelRootDir . '/../src'
                    );
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
                    $adminID = $container->getParameter('edgar_ez_tools.adminid');
                    /** @var Repository $repository */
                    $repository = $container->get('ezpublish.api.repository');
                    $repository->setCurrentUser($repository->getUserService()->loadUser($adminID));

                    $this->validateParameters($parameters);

                    $extensionAlias = strtolower(
                        ProjectGenerator::CUSTOMERS . '_' . $parameters['customerName'] . '_' . CustomerGenerator::SITES
                    );
                    $roleCreatorID = $container->getParameter(
                        'edgarez_sb.customer.' . $extensionAlias . '.default.customer_user_creator_role_id'
                    );
                    $roleEditorID = $container->getParameter(
                        'edgarez_sb.customer.' . $extensionAlias . '.default.customer_user_editor_role_id'
                    );

                    $roleCreator = $this->roleService->loadRole($roleCreatorID);
                    $roleEditor = $this->roleService->loadRole($roleEditorID);

                    $basename = ProjectGenerator::MAIN;
                    $extensionAlias = 'edgarez_sb.' . strtolower($basename);
                    $vendorName = $container->getParameter($extensionAlias . '.default.vendor_name');

                    $siteaccessName = strtolower(
                        $vendorName . '_' . $parameters['customerName'] . '_' . $parameters['siteName']
                    );
                    $this->siteService->addSiteaccessLimitation($roleCreator, $roleEditor, $siteaccessName);
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

                    $site = $this->locationService->loadLocation($parameters['siteID']);

                    $contentInfo = $site->getContentInfo();
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

    public function getMessage()
    {
        return $this->message;
    }
}
