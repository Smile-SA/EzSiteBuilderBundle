<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EzSystems\PlatformUIBundle\Controller\Controller as BaseController;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

class DashboardController extends BaseController
{
    private $searchService;
    private $contentTypeService;

    public function __construct(SearchService $searchService, ContentTypeService $contentTypeService)
    {
        $this->searchService = $searchService;
        $this->contentTypeService = $contentTypeService;
    }

    public function dashboardAction()
    {
        return $this->render('EdgarEzSiteBuilderBundle:dashboard:dashboard.html.twig', []);
    }
}
