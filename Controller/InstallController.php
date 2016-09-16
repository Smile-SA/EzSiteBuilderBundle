<?php

namespace Smile\EzSiteBuilderBundle\Controller;

use Smile\EzSiteBuilderBundle\Data\Install\InstallData;
use Smile\EzSiteBuilderBundle\Data\Mapper\InstallMapper;
use Smile\EzSiteBuilderBundle\Entity\SiteBuilderTask;
use Smile\EzSiteBuilderBundle\Form\ActionDispatcher\InstallDispatcher;
use Smile\EzSiteBuilderBundle\Form\Type\InstallType;
use Smile\EzSiteBuilderBundle\Service\InstallService;
use Smile\EzSiteBuilderBundle\Service\SecurityService;
use Smile\EzSiteBuilderBundle\Values\Content\Install;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class InstallController extends BaseController
{
    /** @var InstallService $installService */
    protected $installService;

    /** @var InstallDispatcher $actionDispatcher */
    protected $actionDispatcher;

    /** @var InstallData $data */
    protected $data;

    protected $tabItems;

    /** @var SecurityService $securityService */
    protected $securityService;

    public function __construct(
        InstallService $installService,
        InstallDispatcher $actionDispatcher,
        $tabItems,
        SecurityService $securityService
    ) {
        $this->installService = $installService;
        $this->actionDispatcher = $actionDispatcher;
        $this->tabItems = $tabItems;
        $this->securityService = $securityService;
    }

    public function installAction(Request $request)
    {
        $actionUrl = $this->generateUrl('smileezsb_sb', ['tabItem' => 'dashboard']);
        if (!$this->securityService->checkAuthorization('install')) {
            return $this->redirectAfterFormPost($actionUrl);
        }

        $actionUrl = $this->generateUrl('smileezsb_sb', ['tabItem' => 'dashboard']);
        $form = $this->getForm($request);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dispatchFormAction($this->actionDispatcher, $form, $this->data, array(
                'vendorName' => $this->data->vendorName,
                'contentLocationID' => $this->data->contentLocationID,
                'mediaLocationID' => $this->data->mediaLocationID,
                'userLocationID' => $this->data->userLocationID,
            ));

            if ($response = $this->actionDispatcher->getResponse()) {
                return $response;
            }

            $this->initTask($form);

            return $this->redirectAfterFormPost($actionUrl);
        }

        $this->getErrors($form, 'smileezsb_form_install');

        $tabItems = array($this->tabItems[0], $this->tabItems[1]);
        return $this->render('SmileEzSiteBuilderBundle:sb:index.html.twig', [
            'tab_items' => $tabItems,
            'tab_item_selected' => 'install',
            'params' => array('install' => $form->createView()),
            'hasErrors' => true
        ]);
    }

    protected function getForm(Request $request)
    {
        $install = new Install([
            'vendorName' => 'Foo',
            'contentLocationID' => 0,
            'mediaLocationID' => 0,
            'userLocationID' => 0
        ]);
        $this->data = (new InstallMapper())->mapToFormData($install);

        return $this->createForm(new InstallType($this->installService), $this->data);
    }

    protected function initTask(Form $form)
    {
        /** @var InstallData $data */
        $data = $form->getData();

        $action = array(
            'service'    => 'project',
            'command'    => 'install',
            'parameters' => array(
                'vendorName'        => $data->vendorName,
                'contentLocationID' => $data->contentLocationID,
                'mediaLocationID'   => $data->mediaLocationID,
                'userLocationID'    => $data->userLocationID,
            )
        );

        $task = new SiteBuilderTask();
        $this->submitTask($task, $action);
    }
}
