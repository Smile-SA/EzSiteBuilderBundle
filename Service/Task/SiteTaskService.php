<?php

namespace Smile\EzSiteBuilderBundle\Service\Task;

use Smile\EzSiteBuilderBundle\Command\Validators;
use Smile\EzSiteBuilderBundle\Generator\CustomerGenerator;
use Smile\EzSiteBuilderBundle\Generator\ProjectGenerator;
use Smile\EzSiteBuilderBundle\Generator\SiteGenerator;
use Smile\EzSiteBuilderBundle\Service\SiteService;
use Smile\EzToolsBundle\Service\Role;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\FieldType\Checkbox\Value;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
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

    /** @var UserService $userService */
    protected $userService;

    /** @var Role $role */
    protected $role;

    /** @var string $kernelRootDir */
    protected $kernelRootDir;

    /** @var int $anonymousUserID */
    protected $anonymousUserID;

    public function __construct(
        Filesystem $filesystem,
        Kernel $kernel,
        LocationService $locationService,
        ContentService $contentService,
        UserService $userService,
        Role $role,
        SiteService $siteService,
        RoleService $roleService,
        $kernelRootDir
    ) {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->userService = $userService;
        $this->role = $role;
        $this->siteService = $siteService;
        $this->roleService = $roleService;
        $this->kernelRootDir = $kernelRootDir;

        $this->message = false;
    }

    public function setAnonymousUserID($anonymousUserID)
    {
        $this->anonymousUserID = $anonymousUserID;
    }

    public function validateParameters($parameters)
    {
        try {
            Validators::validateCustomerName($parameters['customerName']);
            Validators::validateLocationID($parameters['customerContentLocationID']);
            Validators::validateLocationID($parameters['customerMediaLocationID']);
            Validators::validateSiteName($parameters['siteName']);

            foreach ($parameters['sites'] as $site) {
                Validators::validateHost($site['host']);
                Validators::validateSiteaccessSuffix($site['suffix']);
            }

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

    public function execute($command, array $parameters, Container $container, $userID)
    {
        switch ($command) {
            case 'generate':
                try {
                    $this->validateParameters($parameters);

                    $basename = ProjectGenerator::MAIN;
                    $extensionAlias = 'smileez_sb.' . strtolower($basename);
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
                        $parameters['sites'],
                        $siteLocationID,
                        $mediaSiteLocationID,
                        $vendorName,
                        $parameters['siteName'],
                        $parameters['customerName'],
                        $modelLocation->contentInfo->name,
                        $excludeUriPrefixes,
                        $this->kernelRootDir . '/../src'
                    );

                    $this->cacheClear($this->kernel);
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
                    $adminID = $container->getParameter('smile_ez_tools.adminid');
                    /** @var Repository $repository */
                    $repository = $container->get('ezpublish.api.repository');
                    $repository->setCurrentUser($repository->getUserService()->loadUser($adminID));

                    $this->validateParameters($parameters);

                    $extensionAlias = strtolower(
                        ProjectGenerator::CUSTOMERS . '_' . $parameters['customerName'] . '_' . CustomerGenerator::SITES
                    );
                    $roleCreatorID = $container->getParameter(
                        'smileez_sb.customer.' . $extensionAlias . '.default.customer_user_creator_role_id'
                    );
                    $roleEditorID = $container->getParameter(
                        'smileez_sb.customer.' . $extensionAlias . '.default.customer_user_editor_role_id'
                    );

                    $roleCreator = $this->roleService->loadRole($roleCreatorID);
                    $roleEditor = $this->roleService->loadRole($roleEditorID);

                    $basename = ProjectGenerator::MAIN;
                    $extensionAlias = 'smileez_sb.' . strtolower($basename);
                    $vendorName = $container->getParameter($extensionAlias . '.default.vendor_name');

                    $siteaccessNames = array();
                    foreach ($parameters['sites'] as $languageCode => $site) {
                        $siteaccessNames[] = strtolower(
                            $vendorName . '_' . $parameters['customerName'] . '_' . $parameters['siteName'] . '_' .
                            implode(explode('-', $languageCode))
                        );
                    }

                    $this->siteService->addSiteaccessLimitation($roleCreator, $roleEditor, $siteaccessNames);
                    $this->cacheClear($this->kernel);
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
                    $adminID = $container->getParameter('smile_ez_tools.adminid');
                    /** @var Repository $repository */
                    $repository = $container->get('ezpublish.api.repository');
                    $repository->setCurrentUser($repository->getUserService()->loadUser($adminID));

                    $this->validateActivateParameters($parameters);

                    $site = $this->locationService->loadLocation($parameters['siteID']);
                    $parent = $this->locationService->loadLocation($site->parentLocationId);

                    $siteaccessGroups = $container->getParameter('ezpublish.siteaccess.groups');
                    $siteaccessGroupName = strtolower('smileezsb_customer_' . $parent->contentInfo->name . '_' . $site->contentInfo->name);
                    $siteaccessGroup = array();

                    if (isset($siteaccessGroups[$siteaccessGroupName])) {
                        $siteaccessGroup = $siteaccessGroups[$siteaccessGroupName];
                    }

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

                    /**
                     * update Anonymous user/login to add
                     * new siteaccess available
                     */
                    $policies = $this->roleService->loadPoliciesByUserId($this->anonymousUserID);
                    foreach ($policies as $policy) {
                        if ($policy->module == 'user' && $policy->function == 'login') {
                            $siteaccess = array();
                            $limitations = $policy->getLimitations();
                            foreach ($limitations as $limitation) {
                                if ($limitation->getIdentifier() == Limitation::SITEACCESS) {
                                    $siteaccessLogin = $limitation->limitationValues;
                                    foreach ($siteaccessLogin as $s) {
                                        if (!empty($s)) {
                                            $siteaccess[] = $s;
                                        }
                                    }

                                    foreach ($siteaccessGroup as $siteaccessName) {
                                        $siteaccess[] = sprintf('%u', crc32($siteaccessName));
                                    }
                                }
                            }
                            $role = $this->roleService->loadRole($policy->roleId);
                            $this->role->addSiteaccessLimitation($role, $siteaccess);
                            break;
                        }
                    }
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
