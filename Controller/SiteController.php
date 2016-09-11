<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EdgarEz\SiteBuilderBundle\Data\Mapper\SiteActivateMapper;
use EdgarEz\SiteBuilderBundle\Data\Mapper\SiteMapper;
use EdgarEz\SiteBuilderBundle\Data\Site\SiteActivateData;
use EdgarEz\SiteBuilderBundle\Data\Site\SiteData;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EdgarEz\SiteBuilderBundle\Form\ActionDispatcher\SiteActivateDispatcher;
use EdgarEz\SiteBuilderBundle\Form\ActionDispatcher\SiteDispatcher;
use EdgarEz\SiteBuilderBundle\Form\Type\SiteActivateType;
use EdgarEz\SiteBuilderBundle\Form\Type\SiteType;
use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Service\SecurityService;
use EdgarEz\SiteBuilderBundle\Values\Content\Site;
use EdgarEz\SiteBuilderBundle\Values\Content\SiteActivate;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class SiteController extends BaseController
{
    /** @var LocationService $locationService */
    protected $locationService;

    /** @var SearchService $searchService */
    protected $searchService;

    /** @var SiteDispatcher $actionDispatcher */
    protected $actionDispatcher;

    /** @var SiteActivateDispatcher $activateActionDispatcher */
    protected $activateActionDispatcher;

    /** @var SiteData $data */
    protected $data;

    /** @var SiteActivateData $dataActivate */
    protected $dataActivate;

    protected $tabItems;

    /** @var SecurityService $securityService */
    protected $securityService;

    public function __construct(
        LocationService $locationService,
        SearchService $searchService,
        SiteDispatcher $actionDispatcher,
        SiteActivateDispatcher $activateActionDispatcher,
        $tabItems,
        SecurityService $securityService
    ) {
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->actionDispatcher = $actionDispatcher;
        $this->activateActionDispatcher = $activateActionDispatcher;
        $this->tabItems = $tabItems;
        $this->securityService = $securityService;
    }

    public function generateAction(Request $request)
    {
        $actionUrl = $this->generateUrl('edgarezsb_sb', ['tabItem' => 'dashboard']);
        if (!$this->securityService->checkAuthorization('sitegenerate')) {
            return $this->redirectAfterFormPost($actionUrl);
        }

        $site = $request->request->get('edgarezsb_forms_sites');
        $sites = $request->request->get('edgarezsb_forms_site');

        $this->initTask($site, $sites);
        $this->initTask($site, $sites, 'policy', true);
        return $this->redirectAfterFormPost($actionUrl);
    }

    public function listAction()
    {
        /** @var SearchResult $datas */
        $datas = $this->getSites();

        $sites = array();
        if ($datas->totalCount) {
            foreach ($datas->searchHits as $data) {
                $sites[] = array(
                    'data' => $data,
                    'form' => $this->getActivateForm($data->valueObject->contentInfo->mainLocationId)->createView()
                );
            }
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/site/list.html.twig', [
            'totalCount' => $datas->totalCount,
            'datas' => $sites
        ]);
    }

    public function activateAction(Request $request)
    {
        $actionUrl = $this->generateUrl('edgarezsb_sb', ['tabItem' => 'dashboard']);
        if (!$this->securityService->checkAuthorization('siteactivate')) {
            return $this->redirectAfterFormPost($actionUrl);
        }

        $form = $this->getActivateForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dispatchFormAction($this->activateActionDispatcher, $form, $this->dataActivate, array(
                'siteID' => $this->dataActivate->siteID
            ));

            if ($response = $this->activateActionDispatcher->getResponse()) {
                return $response;
            }

            $this->initActivateTask($form);
            return $this->redirectAfterFormPost($actionUrl);
        }

        $this->getErrors($form, 'edgarezsb_form_siteactivate');

        $tabItems = $this->tabItems;
        unset($tabItems[0]);
        return $this->render('EdgarEzSiteBuilderBundle:sb:index.html.twig', [
            'tab_items' => $tabItems,
            'tab_item_selected' => 'siteactivate',
            'params' => array(),
            'hasErrors' => true
        ]);
    }

    protected function initActivateTask(Form $form)
    {
        /** @var SiteActivateData $data */
        $data = $form->getData();

        $action = array(
            'service'    => 'site',
            'command'    => 'activate',
            'parameters' => array(
                'siteID' => $data->siteID
            )
        );

        $task = new SiteBuilderTask();
        $this->submitTask($task, $action);
    }

    protected function getSites()
    {
        $extensionAlias = strtolower(
            ProjectGenerator::CUSTOMERS . '_' .
            $this->getCustomerName() . '_' .
            CustomerGenerator::SITES
        );
        $query = new Query();
        $locationCriterion = new Query\Criterion\ParentLocationId(
            $this->container->getParameter('edgarez_sb.customer.' . $extensionAlias . '.default.customer_location_id')
        );
        $contentTypeIdentifier = new Query\Criterion\ContentTypeIdentifier('edgar_ez_sb_model');
        $activated = new Query\Criterion\Field('activated', Query\Criterion\Operator::EQ, false);

        $query->filter = new Query\Criterion\LogicalAnd(
            array($locationCriterion, $contentTypeIdentifier, $activated)
        );

        return $this->searchService->findContent($query);
    }

    protected function getActivateForm($siteID = null)
    {
        $siteActivate = new SiteActivate([
            'siteID' => $siteID,
        ]);
        $this->dataActivate = (new SiteActivateMapper())->mapToFormData($siteActivate);

        return $this->createForm(new SiteActivateType($siteID), $this->dataActivate);
    }

    protected function initTask(array $site, array $sites, $type = 'generate', $futur = false)
    {
        $action = array(
            'service'    => 'site',
            'command'    => $type,
            'parameters' => array(
                'model' => $site['model'],
                'customerName' => $site['customerName'],
                'customerContentLocationID' => $site['customerContentLocationID'],
                'customerMediaLocationID' => $site['customerMediaLocationID'],
                'sites' => array()
            )
        );

        foreach ($sites as $languageCode => $site) {
            if (empty($site['siteName']) || empty($site['host']))
                continue;

            $action['parameters']['sites'][$languageCode] = array(
                'name' => $site['siteName'],
                'host' => $site['host'],
                'suffix' => $site['suffix']
            );
        }

        if ($futur) {
            $this->submitFuturTask($action);
        } else {
            $task = new SiteBuilderTask();
            $this->submitTask($task, $action);
        }
    }

    protected function getCustomerName()
    {
        /** @var User $user */
        $user = $this->getUser();
        $userLocation = $this->locationService->loadLocation($user->getAPIUser()->contentInfo->mainLocationId);

        $parent = $this->locationService->loadLocation($userLocation->parentLocationId);
        return $parent->contentInfo->name;
    }
}
