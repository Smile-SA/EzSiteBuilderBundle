<?php

namespace EdgarEz\SiteBuilderBundle\Service;

use EdgarEz\ToolsBundle\Service\Content;
use EdgarEz\ToolsBundle\Service\Role;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\User\Limitation;

class SiteService
{
    private $locationService;
    private $urlAliasService;

    private $content;
    private $role;

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

    public function createMediaSiteContent($mediaModelLocationID,$mediaCustomerLocationID, $siteName)
    {
        $mediaSiteLocationID = $this->content->copySubtree($mediaModelLocationID, $mediaCustomerLocationID, $siteName);

        return $mediaSiteLocationID;
    }

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
