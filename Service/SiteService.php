<?php

namespace EdgarEz\SiteBuilderBundle\Service;

use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Generator\SiteGenerator;
use EdgarEz\ToolsBundle\Service\Content;
use EdgarEz\ToolsBundle\Service\Role;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
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

        try {
            $siteLocationID = $this->content->copySubtree($modelLocationID, $customerLocationID, $siteName);

            $returnValue['siteLocationID'] = $siteLocationID;
            $newLocation = $this->locationService->loadLocation($siteLocationID);

            $contentPath = $this->urlAliasService->reverseLookup($newLocation, $newLocation->getContentInfo()->mainLanguageCode)->path;
            $returnValue['excludeUriPrefixes'] = trim($contentPath, '/') . '/';

            return $returnValue;
        } catch (NotFoundException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (\RuntimeException $e) {
            throw $e;
        }
     }

    /**
     * Create site media structure
     *
     * @param int $mediaModelLocationID media model content root location ID
     * @param int $mediaCustomerLocationID media customer content root location ID
     * @param string $siteName site name
     * @return array site media root location ID
     */
     public function createMediaSiteContent($mediaCustomerLocationID, $mediaModelLocationID, $siteName)
    {
        try {
            $mediaSiteLocationID = $this->content->copySubtree($mediaModelLocationID, $mediaCustomerLocationID, $siteName);

            return array(
                'mediaSiteLocationID' => $mediaSiteLocationID
            );
        } catch (\RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * Add siteaccess limitation to user/login policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $roleCreator eZ Role for user creator
     * @param \eZ\Publish\API\Repository\Values\User\Role $roleEditor eZ Role for user editor
     * @param string $siteaccessName siteaccess name
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
                        $siteaccessLogin = $limitation->limitationValues;
                        foreach ($siteaccessLogin as $s) {
                            if (!empty($s)) {
                                $siteaccess[] = $s;
                            }
                        }
                        $siteaccess[] = sprintf('%u', crc32($siteaccessName));
                    }
                }
            }
        }

        try {
            $this->role->addSiteaccessLimitation($roleCreator, $siteaccess);
        } catch (\RuntimeException $e) {
            throw $e;
        }

        $siteaccess = array();
        $policies = $roleEditor->getPolicies();
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
                        $siteaccess[] = sprintf('%u', crc32($siteaccessName));
                    }
                }
            }
        }
        try {
            $this->role->addSiteaccessLimitation($roleEditor, $siteaccess);
        } catch (\RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * Check if a site bundle already exists for a specific customer
     *
     * @param string $siteName
     * @param string $customerName
     * @param string $vendorName
     * @param string $dir
     * @return bool true|false if site bundle already exists for a specific customer
     */
    public function exists($siteName, $customerName, $vendorName, $dir)
    {
        return file_exists($dir . '/' . $vendorName . '/' . ProjectGenerator::CUSTOMERS . '/' . $customerName . '/' . CustomerGenerator::SITES . '/' . $siteName . 'Bundle');
    }
}
