<?php

namespace Smile\EzSiteBuilderBundle\Controller;

use Smile\EzSiteBuilderBundle\Data\Mapper\UserMapper;
use Smile\EzSiteBuilderBundle\Data\User\UserData;
use Smile\EzSiteBuilderBundle\Entity\SiteBuilderTask;
use Smile\EzSiteBuilderBundle\Form\ActionDispatcher\UserDispatcher;
use Smile\EzSiteBuilderBundle\Form\Type\UserType;
use Smile\EzSiteBuilderBundle\Service\SecurityService;
use Smile\EzSiteBuilderBundle\Values\Content\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    /** @var UserDispatcher $actionDispatcher */
    protected $actionDispatcher;

    /** @var UserData $data */
    protected $data;

    protected $tabItems;

    /** @var SecurityService $securityService */
    protected $securityService;

    public function __construct(
        UserDispatcher $actionDispatcher,
        $tabItems,
        SecurityService $securityService
    ) {
        $this->actionDispatcher = $actionDispatcher;
        $this->tabItems = $tabItems;
        $this->securityService = $securityService;
    }

    public function generateAction(Request $request)
    {
        $actionUrl = $this->generateUrl('smileezsb_sb', ['tabItem' => 'dashboard']);
        if (!$this->securityService->checkAuthorization('sitegenerate')) {
            return $this->redirectAfterFormPost($actionUrl);
        }

        $form = $this->getForm($request);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dispatchFormAction($this->actionDispatcher, $form, $this->data, array(
                'userType' => $this->data->userType,
                'userFirstName' => $this->data->userFirstName,
                'userLastName' => $this->data->userLastName,
                'userEmail' => $this->data->userEmail,
            ));

            if ($response = $this->actionDispatcher->getResponse()) {
                return $response;
            }

            $this->initTask($form);

            return $this->redirectAfterFormPost($actionUrl);
        }

        $this->getErrors($form, 'smileezsb_form_user');

        $tabItems = $this->tabItems;
        unset($tabItems[0]);
        return $this->render('SmileEzSiteBuilderBundle:sb:index.html.twig', [
            'tab_items' => $tabItems,
            'tab_item_selected' => 'usergenerate',
            'params' => array('usergenerate' => $form->createView()),
            'hasErrors' => true
        ]);
    }

    protected function getForm(Request $request)
    {
        $user = new User([
            'userType' => 0,
            'userFirstName' => '',
            'userLastName' => '',
            'userEmail' => '',
        ]);
        $this->data = (new UserMapper())->mapToFormData($user);

        return $this->createForm(new UserType(), $this->data);
    }

    protected function initTask(Form $form)
    {
        /** @var UserData $data */
        $data = $form->getData();

        $action = array(
            'service'    => 'user',
            'command'    => 'generate',
            'parameters' => array(
                'userType' => $data->userType,
                'userFirstName' => $data->userFirstName,
                'userLastName' => $data->userLastName,
                'userEmail' => $data->userEmail
            )
        );

        $task = new SiteBuilderTask();
        $this->submitTask($task, $action);
    }
}

