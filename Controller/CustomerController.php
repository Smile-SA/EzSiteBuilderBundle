<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EdgarEz\SiteBuilderBundle\Data\Customer\CustomerData;
use EdgarEz\SiteBuilderBundle\Data\Mapper\CustomerMapper;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EdgarEz\SiteBuilderBundle\Form\ActionDispatcher\CustomerDispatcher;
use EdgarEz\SiteBuilderBundle\Form\Type\CustomerType;
use EdgarEz\SiteBuilderBundle\Service\SecurityService;
use EdgarEz\SiteBuilderBundle\Values\Content\Customer;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class CustomerController extends BaseController
{
    /** @var CustomerDispatcher $actionDispatcher */
    protected $actionDispatcher;

    /** @var CustomerData $data */
    protected $data;

    protected $tabItems;

    /** @var SecurityService $securityService */
    protected $securityService;

    public function __construct(
        CustomerDispatcher $actionDispatcher,
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
        if (!$this->securityService->checkAuthorization('customergenerate')) {
            return $this->redirectAfterFormPost($actionUrl);
        }

        $form = $this->getForm($request);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->actionDispatcher->dispatchFormAction(
                $form,
                $this->data,
                $form->getClickedButton() ? $form->getClickedButton()->getName() : null,
                array(
                    'customerName' => $this->data->customerName,
                    'userFirstName' => $this->data->userFirstName,
                    'userLastName' => $this->data->userLastName,
                    'userEmail' => $this->data->userEmail,
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
                'edgarezsb_form_customer'
            );
        }

        $tabItems = $this->tabItems;
        unset($tabItems[0]);
        return $this->render('EdgarEzSiteBuilderBundle:sb:index.html.twig', [
            'tab_items' => $tabItems,
            'tab_item_selected' => 'customergenerate',
            'params' => array('customergenerate' => $form->createView()),
            'hasErrors' => true
        ]);
    }

    protected function getForm(Request $request)
    {
        $customer = new Customer([
            'customerName' => '',
            'userFirstName' => '',
            'userLastName' => '',
            'userEmail' => '',
        ]);
        $this->data = (new CustomerMapper())->mapToFormData($customer);

        return $this->createForm(new CustomerType(), $this->data);
    }

    protected function initTask(Form $form)
    {
        /** @var CustomerData $data */
        $data = $form->getData();

        $action = array(
            'service'    => 'customer',
            'command'    => 'generate',
            'parameters' => array(
                'customerName' => $data->customerName,
                'userFirstName' => $data->userFirstName,
                'userLastName' => $data->userLastName,
                'userEmail' => $data->userEmail
            )
        );

        $task = new SiteBuilderTask();
        $this->submitTask($task, $action);
    }
}
