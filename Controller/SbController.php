<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EdgarEz\SiteBuilderBundle\Form\Type\CustomerType;
use EdgarEz\SiteBuilderBundle\Form\Type\InstallType;
use EdgarEz\SiteBuilderBundle\Form\Type\ModelType;
use EdgarEz\SiteBuilderBundle\Form\Type\SiteType;
use EzSystems\PlatformUIBundle\Controller\Controller;

class SbController extends Controller
{
    protected $tabItems;

    public function __construct($tabItems)
    {
        $this->tabItems = $tabItems;
    }

    public function sbAction($tabItem)
    {
        $installed = $this->container->hasParameter('edgar_ez_site_builder.installed') ? $this->container->getParameter('edgar_ez_site_builder.installed') : false;
        $tabItems = $this->tabItems;

        if (!$installed) {
            $tabItems = array($tabItems[0], $tabItems[1]);
        } else {
            unset($tabItems[0]);
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:index.html.twig', [
            'installed' => $installed,
            'tab_items' => $tabItems,
            'tab_item_selected' => $tabItem
        ]);
    }

    public function tabAction($tabItem)
    {
        $params = array();
        switch ($tabItem) {
            case 'install':
                $params['installForm'] = $this->createForm(
                    new InstallType()
                )->createView();
                break;
            case 'dashboard':
                $params['user_id'] = $this->getUser()->getAPIUser()->getUserId();
                break;
            case 'customergenerate':
                $params['customerForm'] = $this->createForm(
                    new CustomerType()
                )->createView();
                break;
            case 'modelgenerate':
                $params['modelForm'] = $this->createForm(
                    new ModelType()
                )->createView();
                break;
            case 'sitegenerate':
                $params['siteForm'] = $this->createForm(
                    new SiteType(
                        $this->container->get('ezpublish.api.service.location'),
                        $this->container->get('ezpublish.api.service.search'),
                        $this->container->getParameter('edgarez_sb.project.default.models_location_id')
                    )
                )->createView();
                break;
            default:
                break;
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/' . $tabItem . '.html.twig', [
            'tab_items' => $this->tabItems,
            'tab_item' => $tabItem,
            'params' => $params
        ]);
    }
}
