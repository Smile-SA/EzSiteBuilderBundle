<?php

namespace Smile\EzSiteBuilderBundle\Controller;

use Smile\EzSiteBuilderBundle\Data\Mapper\SiteActivateMapper;
use Smile\EzSiteBuilderBundle\Data\Site\SiteActivateData;
use Smile\EzSiteBuilderBundle\Data\Site\SiteData;
use Smile\EzSiteBuilderBundle\Entity\SiteBuilderTask;
use Smile\EzSiteBuilderBundle\Form\ActionDispatcher\SiteActivateDispatcher;
use Smile\EzSiteBuilderBundle\Form\ActionDispatcher\SiteDispatcher;
use Smile\EzSiteBuilderBundle\Form\Type\SiteActivateType;
use Smile\EzSiteBuilderBundle\Generator\CustomerGenerator;
use Smile\EzSiteBuilderBundle\Generator\ProjectGenerator;
use Smile\EzSiteBuilderBundle\Service\SecurityService;
use Smile\EzSiteBuilderBundle\Values\Content\SiteActivate;
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
        $actionUrl = $this->generateUrl('smileezsb_sb', ['tabItem' => 'dashboard']);
        if (!$this->securityService->checkAuthorization('sitegenerate')) {
            return $this->redirectAfterFormPost($actionUrl);
        }

        $site = $request->request->get('smileezsb_forms_sites');
        $sites = $request->request->get('smileezsb_forms_site');

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

        return $this->render('SmileEzSiteBuilderBundle:sb:tab/site/list.html.twig', [
            'totalCount' => $datas->totalCount,
            'datas' => $sites
        ]);
    }

    public function activateAction(Request $request)
    {
        $actionUrl = $this->generateUrl('smileezsb_sb', ['tabItem' => 'dashboard']);
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

        $this->getErrors($form, 'smileezsb_form_siteactivate');

        $tabItems = $this->tabItems;
        unset($tabItems[0]);
        return $this->render('SmileEzSiteBuilderBundle:sb:index.html.twig', [
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
            $this->container->getParameter('smileez_sb.customer.' . $extensionAlias . '.default.customer_location_id')
        );
        $contentTypeIdentifier = new Query\Criterion\ContentTypeIdentifier('smile_ez_sb_model');
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
                'siteName' => $site['siteName'],
                'customerName' => $site['customerName'],
                'customerContentLocationID' => $site['customerContentLocationID'],
                'customerMediaLocationID' => $site['customerMediaLocationID'],
                'sites' => array()
            )
        );

        foreach ($sites as $languageCode => $site) {
            if (empty($site['host']))
                continue;

            $action['parameters']['sites'][$languageCode] = array(
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
