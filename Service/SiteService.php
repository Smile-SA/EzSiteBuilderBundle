<?php

namespace EdgarEz\SiteBuilderBundle\Service;

use EdgarEz\ToolsBundle\Service\Content;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\URLAliasService;

class SiteService
{
    private $locationService;
    private $urlAliasService;

    private $content;

    public function __construct(
        LocationService $locationService,
        URLAliasService $urlAliasService,
        Content $content
    )
    {
        $this->locationService = $locationService;
        $this->urlAliasService = $urlAliasService;
        $this->content = $content;
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
}
