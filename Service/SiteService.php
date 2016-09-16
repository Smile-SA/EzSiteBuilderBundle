<?php

namespace Smile\EzSiteBuilderBundle\Service;

use Smile\EzSiteBuilderBundle\Generator\CustomerGenerator;
use Smile\EzSiteBuilderBundle\Generator\ProjectGenerator;
use Smile\EzToolsBundle\Service\Content;
use Smile\EzToolsBundle\Service\Role;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * Class SiteService
 * @package Smile\EzSiteBuilderBundle\Service
 */
class SiteService
{
    /** @var LocationService $locationService eZ Location Service */
    private $locationService;

    /** @var URLAliasService $urlAliasService eZ URLAlias Service */
    private $urlAliasService;

    /** @var ContentService $contentService */
    private $contentService;

    /** @var LanguageService $languageService */
    private $languageService;

    /** @var Content $content SmileEz Content Service */
    private $content;

    /** @var Role $role SmileEz Role Service */
    private $role;

    /**
     * SiteService constructor.
     *
     * @param LocationService $locationService eZ Location Service
     * @param URLAliasService $urlAliasService eZ URLAlias Service
     * @param Content $content SmileEz Content Service
     * @param Role $role SmileEz Role Service
     */
    public function __construct(
        LocationService $locationService,
        URLAliasService $urlAliasService,
        ContentService $contentService,
        LanguageService $languageService,
        Content $content,
        Role $role
    ) {
        $this->locationService = $locationService;
        $this->urlAliasService = $urlAliasService;
        $this->contentService = $contentService;
        $this->languageService = $languageService;
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
            $content = $this->contentService->loadContent($newLocation->contentId);

            $languages = $this->languageService->loadLanguages();

            $returnValue['excludeUriPrefixes'] = array();
            foreach ($languages as $language) {
                $languageCode = $language->languageCode;
                $newVersionInfo = $this->contentService->createContentDraft(
                    $newLocation->contentInfo
                )->getVersionInfo();

                $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
                foreach ($content->getFields() as $key => $field) {
                    $fieldValue = $field->value;
                    if ($field->fieldDefIdentifier == 'title')
                        $fieldValue = $siteName;
                    $contentUpdateStruct->setField($field->fieldDefIdentifier, $fieldValue, $languageCode);
                }
                $contentUpdateStruct->initialLanguageCode = $languageCode;
                $contentDraft = $this->contentService->updateContent($newVersionInfo, $contentUpdateStruct);
                $this->contentService->publishVersion($contentDraft->versionInfo);

                $contentPath = $this->urlAliasService->reverseLookup(
                    $newLocation,
                    $languageCode
                )->path;
                $returnValue['excludeUriPrefixes'][$languageCode] = trim($contentPath, '/') . '/';
            }

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
            $mediaSiteLocationID = $this->content->copySubtree(
                $mediaModelLocationID,
                $mediaCustomerLocationID,
                $siteName
            );

            $newLocation = $this->locationService->loadLocation($mediaSiteLocationID);
            $content = $this->contentService->loadContent($newLocation->contentId);

            $languages = $this->languageService->loadLanguages();
            foreach ($languages as $language) {
                $languageCode = $language->languageCode;
                $newVersionInfo = $this->contentService->createContentDraft(
                    $newLocation->contentInfo
                )->getVersionInfo();

                $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
                foreach ($content->getFields() as $key => $field) {
                    $fieldValue = $field->value;
                    if ($field->fieldDefIdentifier == 'title')
                        $fieldValue = $siteName;
                    $contentUpdateStruct->setField($field->fieldDefIdentifier, $fieldValue, $languageCode);
                }
                $contentUpdateStruct->initialLanguageCode = $languageCode;
                $contentDraft = $this->contentService->updateContent($newVersionInfo, $contentUpdateStruct);
                $this->contentService->publishVersion($contentDraft->versionInfo);
            }

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
    ) {
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
        return file_exists(
            $dir . '/' . $vendorName . '/' . ProjectGenerator::CUSTOMERS . '/' . $customerName .
            '/' . CustomerGenerator::SITES . '/' . $siteName . 'Bundle'
        );
    }
}
