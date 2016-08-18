<?php

namespace EdgarEz\SiteBuilderBundle\Service;

use EdgarEz\ToolsBundle\Service\Content;
use EdgarEz\ToolsBundle\Service\Role;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * Class SiteService
 * @package EdgarEz\SiteBuilderBundle\Service
 */
class SiteService
{
    /** @var LocationService $locationService eZ Location Service */
    private $locationService;

    /** @var URLAliasService $urlAliasService eZ URLAlias Service */
    private $urlAliasService;

    /** @var Content $content EdgarEz Content Service */
    private $content;

    /** @var Role $role EdgarEz Role Service */
    private $role;

    /**
     * SiteService constructor.
     *
     * @param LocationService $locationService eZ Location Service
     * @param URLAliasService $urlAliasService eZ URLAlias Service
     * @param Content $content EdgarEz Content Service
     * @param Role $role EdgarEz Role Service
     */
    public function __construct(
        LocationService $locationService,
        URLAliasService $urlAliasService,
        Content $content,
        Role $role
    )
    {
        $this->locationService = $locationService;
        $this->urlAliasService = $urlAliasService;
        $this->content = $content;
        $this->role = $role;
    }

    /**
     * Create site content structure
     *
     * @param int $customerLocationID customer content root location ID
     * @param int $modelLocationID model content root location ID
     * @param string $siteName site name
     * @return array site content location ID and siteaccess path prefix
     */
    public function createSiteContent($customerLocationID, $modelLocationID, $siteName)
    {
        $returnValue = array();

        $siteLocationID = $this->content->copySubtree($modelLocationID, $customerLocationID, $siteName);

        $returnValue['siteLocationID'] = $siteLocationID;
        $newLocation = $this->locationService->loadLocation($siteLocationID);

        $contentPath = $this->urlAliasService->reverseLookup($newLocation, $newLocation->getContentInfo()->mainLanguageCode)->path;
        $returnValue['excludeUriPrefixes'] = trim($contentPath, '/') . '/';

        return $returnValue;
     }

    /**
     * Create site media structure
     *
     * @param int $mediaModelLocationID media model content root location ID
     * @param int $mediaCustomerLocationID media customer content root location ID
     * @param string $siteName site name
     * @return int site media root location ID
     */
     public function createMediaSiteContent($mediaModelLocationID,$mediaCustomerLocationID, $siteName)
    {
        $mediaSiteLocationID = $this->content->copySubtree($mediaModelLocationID, $mediaCustomerLocationID, $siteName);

        return $mediaSiteLocationID;
    }

    /**
     * Add siteaccess limitation to user/login policy
     * 
     * @param \eZ\Publish\API\Repository\Values\User\Role $roleCreator eZ Role for user creator
     * @param \eZ\Publish\API\Repository\Values\User\Role $roleEditor eZ Role for user editor
     * @param $siteaccessName siteaccess name
     */
    public function addSiteaccessLimitation(
        \eZ\Publish\API\Repository\Values\User\Role $roleCreator,
        \eZ\Publish\API\Repository\Values\User\Role $roleEditor,
        $siteaccessName
    )
    {
        $siteaccess = array();
        $policies = $roleCreator->getPolicies();
        foreach ($policies as $policy) {
            if ($policy->module == 'user' && $policy->function == 'login') {
                $limitations = $policy->getLimitations();
                foreach ($limitations as $limitation) {
                    if ($limitation->getIdentifier() == Limitation::SITEACCESS) {
                        $siteaccess = $limitation->limitationValues;
                        $siteaccess[] = sprintf('%u', crc32($siteaccessName));
                    }
                }
            }
        }
        $this->role->addSiteaccessLimitation($roleCreator, $siteaccess);

        $siteaccess = array();
        $policies = $roleEditor->getPolicies();
        foreach ($policies as $policy) {
            if ($policy->module == 'user' && $policy->function == 'login') {
                $limitations = $policy->getLimitations();
                foreach ($limitations as $limitation) {
                    if ($limitation->getIdentifier() == Limitation::SITEACCESS) {
                        $siteaccess = $limitation->limitationValues;
                        $siteaccess[] = sprintf('%u', crc32($siteaccessName));
                    }
                }
            }
        }
        $this->role->addSiteaccessLimitation($roleEditor, $siteaccess);
    }
}
