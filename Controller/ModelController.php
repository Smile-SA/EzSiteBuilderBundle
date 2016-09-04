<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EdgarEz\SiteBuilderBundle\Data\Mapper\ModelActivateMapper;
use EdgarEz\SiteBuilderBundle\Data\Mapper\ModelMapper;
use EdgarEz\SiteBuilderBundle\Data\Model\ModelActivateData;
use EdgarEz\SiteBuilderBundle\Data\Model\ModelData;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EdgarEz\SiteBuilderBundle\Form\ActionDispatcher\ModelActivateDispatcher;
use EdgarEz\SiteBuilderBundle\Form\ActionDispatcher\ModelDispatcher;
use EdgarEz\SiteBuilderBundle\Form\Type\ModelActivateType;
use EdgarEz\SiteBuilderBundle\Form\Type\ModelType;
use EdgarEz\SiteBuilderBundle\Service\SecurityService;
use EdgarEz\SiteBuilderBundle\Values\Content\Model;
use EdgarEz\SiteBuilderBundle\Values\Content\ModelActivate;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class ModelController extends BaseController
{
    /** @var ModelDispatcher $actionDispatcher */
    protected $actionDispatcher;

    /** @var ModelActivateDispatcher $activateActionDispatcher */
    protected $activateActionDispatcher;

    /** @var ModelData $data */
    protected $data;

    /** @var ModelActivateData $dataActivate */
    protected $dataActivate;

    protected $tabItems;

    /** @var SecurityService $securityService */
    protected $securityService;

    /** @var SearchService $searchService */
    protected $searchService;

    public function __construct(
        ModelDispatcher $actionDispatcher,
        ModelActivateDispatcher $activateActionDispatcher,
        $tabItems,
        SecurityService $securityService,
    SearchService $searchService
    ) {
        $this->actionDispatcher = $actionDispatcher;
        $this->activateActionDispatcher = $activateActionDispatcher;
        $this->tabItems = $tabItems;
        $this->securityService = $securityService;
        $this->searchService = $searchService;
    }

    public function generateAction(Request $request)
    {
        $actionUrl = $this->generateUrl('edgarezsb_sb', ['tabItem' => 'dashboard']);
        if (!$this->securityService->checkAuthorization('modelgenerate')) {
            return $this->redirectAfterFormPost($actionUrl);
        }

        $form = $this->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dispatchFormAction($this->actionDispatcher, $form, $this->data, array(
                'modelName' => $this->data->modelName
            ));

            if ($response = $this->actionDispatcher->getResponse()) {
                return $response;
            }

            $this->initTask($form);
            $this->initPolicyTask($form);
            return $this->redirectAfterFormPost($actionUrl);
        }

        $this->getErrors($form, 'edgarezsb_form_model');

        $tabItems = $this->tabItems;
        unset($tabItems[0]);
        return $this->render('EdgarEzSiteBuilderBundle:sb:index.html.twig', [
            'tab_items' => $tabItems,
            'tab_item_selected' => 'modelgenerate',
            'params' => array('modelgenerate' => $form->createView()),
            'hasErrors' => true
        ]);
    }

    public function activateAction(Request $request)
    {
        $actionUrl = $this->generateUrl('edgarezsb_sb', ['tabItem' => 'dashboard']);
        if (!$this->securityService->checkAuthorization('modelactivate')) {
            return $this->redirectAfterFormPost($actionUrl);
        }

        $form = $this->getActivateForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dispatchFormAction($this->activateActionDispatcher, $form, $this->dataActivate, array(
                'modelID' => $this->dataActivate->modelID
            ));

            if ($response = $this->activateActionDispatcher->getResponse()) {
                return $response;
            }

            $this->initActivateTask($form);
            return $this->redirectAfterFormPost($actionUrl);
        }

        $this->getErrors($form, 'edgarezsb_form_modelactivate');

        $tabItems = $this->tabItems;
        unset($tabItems[0]);
        return $this->render('EdgarEzSiteBuilderBundle:sb:index.html.twig', [
            'tab_items' => $tabItems,
            'tab_item_selected' => 'modelactivate',
            'params' => array(),
            'hasErrors' => true
        ]);
    }

    protected function getForm()
    {
        $model = new Model([
            'modelName' => 'Foo',
        ]);
        $this->data = (new ModelMapper())->mapToFormData($model);

        return $this->createForm(new ModelType(), $this->data);
    }

    protected function getActivateForm($modelID = null)
    {
        $modelActivate = new ModelActivate([
            'modelID' => $modelID,
        ]);
        $this->dataActivate = (new ModelActivateMapper())->mapToFormData($modelActivate);

        return $this->createForm(new ModelActivateType($modelID), $this->dataActivate);
    }

    protected function initTask(Form $form)
    {
        /** @var ModelData $data */
        $data = $form->getData();

        $action = array(
            'service'    => 'model',
            'command'    => 'generate',
            'parameters' => array(
                'modelName' => $data->modelName
            )
        );

        $task = new SiteBuilderTask();
        $this->submitTask($task, $action);
    }

    protected function initActivateTask(Form $form)
    {
        /** @var ModelActivateData $data */
        $data = $form->getData();

        $action = array(
            'service'    => 'model',
            'command'    => 'activate',
            'parameters' => array(
                'modelID' => $data->modelID
            )
        );

        $task = new SiteBuilderTask();
        $this->submitTask($task, $action);
    }

    protected function initPolicyTask(Form $form)
    {
        /** @var ModelData $data */
        $data = $form->getData();

        $action = array(
            'service'    => 'model',
            'command'    => 'policy',
            'parameters' => array(
                'modelName' => $data->modelName
            )
        );

        $this->submitFuturTask($action);
    }

    public function listAction()
    {
        /** @var SearchResult $datas */
        $datas = $this->getModels();

        $models = array();
        if ($datas->totalCount) {
            foreach ($datas->searchHits as $data) {
                $models[] = array(
                    'data' => $data,
                    'form' => $this->getActivateForm($data->valueObject->contentInfo->mainLocationId)->createView()
                );
            }
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/model/list.html.twig', [
            'totalCount' => $datas->totalCount,
            'datas' => $models
        ]);
    }

    protected function getModels()
    {
        $query = new Query();
        $locationCriterion = new Query\Criterion\ParentLocationId(
            $this->container->getParameter('edgarez_sb.project.default.models_location_id')
        );
        $contentTypeIdentifier = new Query\Criterion\ContentTypeIdentifier('edgar_ez_sb_model');
        $activated = new Query\Criterion\Field('activated', Query\Criterion\Operator::EQ, false);

        $query->filter = new Query\Criterion\LogicalAnd(
            array($locationCriterion, $contentTypeIdentifier, $activated)
        );

        return $this->searchService->findContent($query);
    }
}
