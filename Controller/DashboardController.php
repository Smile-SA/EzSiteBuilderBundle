<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EzSystems\PlatformUIBundle\Controller\Controller as BaseController;

class DashboardController extends BaseController
{
    protected $tabItems;

    public function __construct($tabItems)
    {
        $this->tabItems = $tabItems;
    }

    public function dashboardAction()
    {
        return $this->render('EdgarEzSiteBuilderBundle:dashboard:dashboard.html.twig', [
            'tab_items' => $this->tabItems
        ]);
    }

    public function tabAction($tabItem, $viewType)
    {
        return $this->render('EdgarEzSiteBuilderBundle:dashboard:tab/' . $tabItem . '.html.twig', [
            'tab_items' => $this->tabItems,
            'tab_item' => $tabItem,
            'view_type' => $viewType
        ]);
    }
}
