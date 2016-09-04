<?php

namespace EdgarEz\SiteBuilderBundle\Service;

use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\ToolsBundle\Service\Content;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\LimitationValidationException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\Core\FieldType\Checkbox\Value;
use eZ\Publish\Core\Repository\Values\User\Policy;
use eZ\Publish\Core\Repository\Values\User\PolicyDraft;
use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ModelService
 * @package EdgarEz\SiteBuilderBundle\Service
 */
class ModelService
{
    /** @var Kernel $kernel symfony kernel interface */
    private $kernel;

    /** @var URLAliasService $urlAliasService eZ URLAlias Service */
    private $urlAliasService;

    /** @var LocationService $locationService eZ Lccation Service */
    private $locationService;

    /** @var RoleService $roleService eZ Role Service */
    private $roleService;

    /** @var Content $content EdgarEz Content Service */
    private $content;

    /** @var \EdgarEz\ToolsBundle\Service\Role EdgarEz Role Service */
    private $role;

    /** @var ContainerInterface $container */
    private $container;

    /**
     * ModelService constructor.
     *
     * @param URLAliasService $urlAliasService eZ URLAlias Service
     * @param LocationService $locationService eZ Location Service
     * @param RoleService $roleService eZ Role Service
     * @param Content $content EdgarEz Content Service
     * @param \EdgarEz\ToolsBundle\Service\Role $role EdgarEz Role Service
     * @param array $siteaccessGroups ezpublish siteaccess groups
     * @param ContainerInterface $container
     */
    public function __construct(
        Kernel $kernel,
        URLAliasService $urlAliasService,
        LocationService $locationService,
        RoleService $roleService,
        Content $content,
        \EdgarEz\ToolsBundle\Service\Role $role,
        ContainerInterface $container
    ) {
        $this->kernel = $kernel;
        $this->urlAliasService = $urlAliasService;
        $this->locationService = $locationService;
        $this->roleService = $roleService;
        $this->content = $content;
        $this->role = $role;
        $this->container = $container;
    }

    /**
     * Add siteaccess limitation to user/login policy
     *
     * @param string $modelName model name
     * @param array $customers customers name list
     */
    public function addSiteaccessLimitation($modelName, array $customers)
    {
        try {
            $rolesCreator = array();
            foreach ($customers as $customer) {
                $parameter = 'edgarez_sb.customer.customers_' .
                    $customer .
                    '_sites.default.customer_user_creator_role_id';
                $roleCreatorID = $this->container->getParameter($parameter);
                $rolesCreator[] = $this->roleService->loadRole($roleCreatorID);
            }

            foreach ($rolesCreator as $roleCreator) {
                $siteaccess = array();

                /** @var Policy[] $policies */
                $policies = $roleCreator->getPolicies();
                foreach ($policies as $policy) {
                    if ($policy->module == 'user' && $policy->function == 'login') {
                        $limitations = $policy->getLimitations();
                        foreach ($limitations as $limitation) {
                            if ($limitation->getIdentifier() == Limitation::SITEACCESS) {
                                $siteaccessLogin = $limitation->limitationValues;
                                foreach ($siteaccessLogin as $s) {
                                    if (!empty($s)) {
                                        $siteaccess[] = $s;
                                    }
                                }
                                $siteaccess[] = sprintf('%u', crc32($modelName));
                            }
                        }
                    }
                }
                $this->role->addSiteaccessLimitation($roleCreator, $siteaccess);
            }
        } catch (UnauthorizedException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (NotFoundException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (\RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * Create Model content structure
     *
     * @param int $modelsLocationID model root location ID
     * @param string $modelName model name
     * @return array
     */
    public function createModelContent($modelsLocationID, $modelName)
    {
        $returnValue = array();

        try {
            $contentDefinition = Yaml::parse(
                file_get_contents(
                    $this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/datas/modelcontent.yml')
                )
            );
            $contentDefinition['parentLocationID'] = $modelsLocationID;
            $contentDefinition['fields']['title']['value'] = $modelName;
            $contentDefinition['fields']['activated']['value'] = new Value(false);
            $contentAdded = $this->content->add($contentDefinition);

            $contentLocation = $this->locationService->loadLocation($contentAdded->contentInfo->mainLocationId);
            $contentPath = $this->urlAliasService->reverseLookup(
                $contentLocation,
                $contentAdded->contentInfo->mainLanguageCode
            )->path;
            $returnValue['excludeUriPrefixes'] = trim($contentPath, '/') . '/';
            $returnValue['modelLocationID'] = $contentAdded->contentInfo->mainLocationId;

            return $returnValue;
        } catch (ParseException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (\RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * Create Model media structure
     *
     * @param int $mediaModelsLocationID model media root location ID
     * @param string $modelName model name
     * @return array
     */
    public function createMediaModelContent($mediaModelsLocationID, $modelName)
    {
        try {
            $contentDefinition = Yaml::parse(
                file_get_contents(
                    $this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/datas/mediamodelcontent.yml')
                )
            );
            $contentDefinition['parentLocationID'] = $mediaModelsLocationID;
            $contentDefinition['fields']['title']['value'] = $modelName;
            $contentAdded = $this->content->add($contentDefinition);

            return array(
                'mediaModelLocationID' => $contentAdded->contentInfo->mainLocationId
            );
        } catch (ParseException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (\RuntimeException $e) {
            throw $e;
        }
    }

    public function updateGlobalRole($modelLocationID, $mediaModelLocationID)
    {
        try {
            /** @var Role $role */
            $role = $this->roleService->loadRoleByIdentifier('SiteBuilder');

            $roleDraft = $this->roleService->createRoleDraft($role);

            /** @var Policy[] $policies */
            $policies = $roleDraft->policies;
            foreach ($policies as $policy) {
                if ($policy->module == 'content' && $policy->function == 'read') {
                    /** @var Limitation[] $limitations */
                    $limitations = $policy->getLimitations();
                    foreach ($limitations as $limitation) {
                        if ($limitation->getIdentifier() == 'Node') {
                            $limitationValues = $limitation->limitationValues;
                            $limitationValues[] = $modelLocationID;
                            $limitationValues[] = $mediaModelLocationID;
                            $limitation->limitationValues = $limitationValues;

                            $policyUpdateStruct = new PolicyUpdateStruct();
                            $policyUpdateStruct->addLimitation($limitation);

                            $policyDraft = new PolicyDraft(
                                [
                                    'innerPolicy' => new Policy(
                                        [
                                            'id' => $policy->id,
                                            'module' => 'content',
                                            'function' => 'read',
                                            'roleId' => $roleDraft->id
                                        ]
                                    )
                                ]
                            );

                            $this->roleService->updatePolicyByRoleDraft(
                                $roleDraft,
                                $policyDraft,
                                $policyUpdateStruct
                            );
                            $this->roleService->publishRoleDraft($roleDraft);
                        }
                    }
                }
            }
        } catch (UnauthorizedException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (NotFoundException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (LimitationValidationException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (InvalidArgumentException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Check if model bundle already exists
     *
     * @param string $modelName
     * @param string $vendorName
     * @param string $dir
     * @return bool true|false if model bundle already exists
     */
    public function exists($modelName, $vendorName, $dir)
    {
        return file_exists($dir . '/' . $vendorName . '/' . ProjectGenerator::MODELS . '/' . $modelName . 'Bundle');
    }
}
