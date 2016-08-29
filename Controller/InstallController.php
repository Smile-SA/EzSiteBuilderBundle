<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use EdgarEz\SiteBuilderBundle\Data\Install\InstallData;
use EdgarEz\SiteBuilderBundle\Data\Mapper\InstallMapper;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EdgarEz\SiteBuilderBundle\Form\ActionDispatcher\InstallDispatcher;
use EdgarEz\SiteBuilderBundle\Form\Type\InstallType;
use EdgarEz\SiteBuilderBundle\Service\SecurityService;
use EdgarEz\SiteBuilderBundle\Values\Content\Install;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class InstallController extends BaseController
{
    /** @var InstallDispatcher $actionDispatcher */
    protected $actionDispatcher;

    /** @var InstallData $data */
    protected $data;

    protected $tabItems;

    /** @var SecurityService $securityService */
    protected $securityService;

    public function __construct(
        InstallDispatcher $actionDispatcher,
        $tabItems,
        SecurityService $securityService
    )
    {
        $this->actionDispatcher = $actionDispatcher;
        $this->tabItems = $tabItems;
        $this->securityService = $securityService;
    }

    public function installAction(Request $request)
    {
        $actionUrl = $this->generateUrl('edgarezsb_sb', ['tabItem' => 'dashboard']);
        if (!$this->securityService->checkAuthorization('install')) {
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
                array(
                    'vendorName' => $this->data->vendorName,
                    'contentLocationID' => $this->data->contentLocationID,
                    'mediaLocationID' => $this->data->mediaLocationID,
                    'userLocationID' => $this->data->userLocationID,
                )
            );

            if ($response = $this->actionDispatcher->getResponse()) {
                return $response;
            }

            $this->initTask($form);

            return $this->redirectAfterFormPost($actionUrl);
        }

        foreach ($form->getErrors(true) as $error) {
            $this->notifyErrorPlural(
                $error->getMessageTemplate(),
                $error->getMessagePluralization(),
                $error->getMessageParameters(),
                'edgarezsb_form_install'
            );
        }

        $tabItems = array($this->tabItems[0], $this->tabItems[1]);
        return $this->render('EdgarEzSiteBuilderBundle:sb:index.html.twig', [
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

        return $this->createForm(new InstallType(), $this->data);
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
