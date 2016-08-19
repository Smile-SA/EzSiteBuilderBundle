<?php

namespace EdgarEz\SiteBuilderBundle\Service;

use EdgarEz\ToolsBundle\Service\Content;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Policy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
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

    /** @var array $siteaccessGroups ezpublish siteaccess groups */
    private $siteaccessGroups;

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
        array $siteaccessGroups,
        ContainerInterface $container
    )
    {
        $this->kernel = $kernel;
        $this->urlAliasService = $urlAliasService;
        $this->locationService = $locationService;
        $this->roleService = $roleService;
        $this->content = $content;
        $this->siteaccessGroups = $siteaccessGroups;
        $this->role = $role;
        $this->container = $container;
    }

    /**
     * Create model content
     *
     * @param int $modelsLocationID model root contentlocation ID
     * @param string $modelName model name
     * @return array model content location ID and path prefix
     */
    public function createModelContent($modelsLocationID, $modelName)
    {
        $returnValue = array();

        $contentDefinition = Yaml::parse(file_get_contents($this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/datas/modelcontent.yml')));
        $contentDefinition['parentLocationID'] = $modelsLocationID;
        $contentDefinition['fields']['title']['value'] = $modelName;
        $contentAdded = $this->content->add($contentDefinition);

        $contentLocation = $this->locationService->loadLocation($contentAdded->contentInfo->mainLocationId);
        $contentPath = $this->urlAliasService->reverseLookup($contentLocation, $contentAdded->contentInfo->mainLanguageCode)->path;
        $returnValue['excludeUriPrefixes'] = trim($contentPath, '/') . '/';
        $returnValue['modelLocationID'] = $contentAdded->contentInfo->mainLocationId;

        return $returnValue;
    }

    /**
     * Create model media content
     *
     * @param int $mediaModelsLocationID model media root location ID
     * @param string $modelName model name
     * @return mixed model media content location ID
     */
    public function createMediaModelContent($mediaModelsLocationID, $modelName)
    {
        $contentDefinition = Yaml::parse(file_get_contents($this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/datas/mediamodelcontent.yml')));
        $contentDefinition['parentLocationID'] = $mediaModelsLocationID;
        $contentDefinition['fields']['title']['value'] = $modelName;
        $contentAdded = $this->content->add($contentDefinition);

        return $contentAdded->contentInfo->mainLocationId;
    }

    /**
     * Add siteaccess limitation to user/login policy
     *
     * @param string $modelName model name
     */
    public function addSiteaccessLimitation($modelName)
    {
        $customers = array();

        $siteaccessGroups = array_keys($this->siteaccessGroups);
        foreach ($siteaccessGroups as $sg) {
            if (strpos($sg, 'edgarezsb_customer_') === 0) {
                $customers[] = substr($sg, strlen('edgarezsb_customer_'));
            }
        }

        $rolesCreator = array();
        foreach ($customers as $customer) {
            $parameter = 'edgarez_sb.customer.customers_' . $customer . '_sites.default.customer_user_creator_role_id';
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
                            $siteaccess = $limitation->limitationValues;
                            $siteaccess[] = sprintf('%u', crc32($modelName));
                        }
                    }
                }
            }
            $this->role->addSiteaccessLimitation($roleCreator, $siteaccess);
        }
    }
}
