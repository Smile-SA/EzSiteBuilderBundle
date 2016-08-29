<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EdgarEz\SiteBuilderBundle\Data\Mapper\ModelMapper;
use EdgarEz\SiteBuilderBundle\Data\Model\ModelData;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EdgarEz\SiteBuilderBundle\Form\ActionDispatcher\ModelDispatcher;
use EdgarEz\SiteBuilderBundle\Form\Type\ModelType;
use EdgarEz\SiteBuilderBundle\Service\SecurityService;
use EdgarEz\SiteBuilderBundle\Values\Content\Model;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class ModelController extends BaseController
{
    /** @var ModelDispatcher $actionDispatcher */
    protected $actionDispatcher;

    /** @var ModelData $data */
    protected $data;

    protected $tabItems;

    /** @var SecurityService $securityService */
    protected $securityService;

    public function __construct(
        ModelDispatcher $actionDispatcher,
        $tabItems,
        SecurityService $securityService
    )
    {
        $this->actionDispatcher = $actionDispatcher;
        $this->tabItems = $tabItems;
        $this->securityService = $securityService;
    }

    public function generateAction(Request $request)
    {
        $actionUrl = $this->generateUrl('edgarezsb_sb', ['tabItem' => 'dashboard']);
        if (!$this->securityService->checkAuthorization('modelgenerate')) {
            return $this->redirectAfterFormPost($actionUrl);
        }

        $actionUrl = $this->generateUrl('edgarezsb_sb', ['tabItem' => 'dashboard']);
        $form = $this->getForm($request);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->actionDispatcher->dispatchFormAction(
                $form,
                $this->data,
                $form->getClickedButton() ? $form->getClickedButton()->getName() : null,
                array('modelName' => $this->data->modelName)
            );

            if ($response = $this->actionDispatcher->getResponse()) {
                return $response;
            }

            $this->initTask($form);
            $this->initPolicyTask($form);

            return $this->redirectAfterFormPost($actionUrl);
        }

        foreach ($form->getErrors(true) as $error) {
            $this->notifyErrorPlural(
                $error->getMessageTemplate(),
                $error->getMessagePluralization(),
                $error->getMessageParameters(),
                'edgarezsb_form_model'
            );
        }

        $tabItems = $this->tabItems;
        unset($tabItems[0]);
        return $this->render('EdgarEzSiteBuilderBundle:sb:index.html.twig', [
            'tab_items' => $tabItems,
            'tab_item_selected' => 'modelgenerate',
            'params' => array('modelgenerate' => $form->createView()),
            'hasErrors' => true
        ]);
    }

    protected function getForm(Request $request)
    {
        $model = new Model([
            'modelName' => 'Foo',
        ]);
        $this->data = (new ModelMapper())->mapToFormData($model);

        return $this->createForm(new ModelType(), $this->data);
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
}
