<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EzSystems\PlatformUIBundle\Controller\Controller as BaseController;

class SbController extends BaseController
{
    protected $tabItems;

    public function __construct($tabItems)
    {
        $this->tabItems = $tabItems;
    }

    public function dashboardAction()
    {
        return $this->render('EdgarEzSiteBuilderBundle:sb:dashboard.html.twig', [
            'tab_items' => $this->tabItems
        ]);
    }

    public function tabAction($tabItem, $viewType)
    {
        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/' . $tabItem . '.html.twig', [
            'tab_items' => $this->tabItems,
            'tab_item' => $tabItem,
            'view_type' => $viewType
        ]);
    }
}
