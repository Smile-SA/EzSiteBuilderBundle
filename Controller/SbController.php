<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EdgarEz\SiteBuilderBundle\Form\Type\InstallType;
use EzSystems\PlatformUIBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Request;

class SbController extends BaseController
{
    protected $tabItems;

    public function __construct($tabItems)
    {
        $this->tabItems = $tabItems;
    }

    public function dashboardAction()
    {
        $installed = $this->container->hasParameter('edgar_ez_site_builder.installed') ? $this->container->getParameter('edgar_ez_site_builder.installed') : false;
        $tabItems = $this->tabItems;

        if (!$installed) {
            $tabItems = array($tabItems[0]);
        } else {
            unset($tabItems[0]);
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:dashboard.html.twig', [
            'installed' => $installed,
            'tab_items' => $tabItems
        ]);
    }

    public function tabAction($tabItem, $viewType)
    {
        $params = array();
        switch ($tabItem) {
            case 'install':
                $params['installForm'] = $this->createForm(
                    new InstallType()
                )->createView();
                break;
            default:
                break;

        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/' . $tabItem . '.html.twig', [
            'tab_items' => $this->tabItems,
            'tab_item' => $tabItem,
            'params' => $params,
            'view_type' => $viewType
        ]);
    }

    public function tabInstallAction()
    {
    }

    public function postInstallAction(Request $request)
    {
        return $this->redirectToRouteAfterFormPost('edgarezsb_dashboard');
    }
}
