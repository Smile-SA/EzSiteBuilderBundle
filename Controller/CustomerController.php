<?php

namespace Smile\EzSiteBuilderBundle\Controller;

use Smile\EzSiteBuilderBundle\Data\Customer\CustomerData;
use Smile\EzSiteBuilderBundle\Data\Mapper\CustomerMapper;
use Smile\EzSiteBuilderBundle\Entity\SiteBuilderTask;
use Smile\EzSiteBuilderBundle\Form\ActionDispatcher\CustomerDispatcher;
use Smile\EzSiteBuilderBundle\Form\Type\CustomerType;
use Smile\EzSiteBuilderBundle\Service\SecurityService;
use Smile\EzSiteBuilderBundle\Values\Content\Customer;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CustomerController
 * @package Smile\EzSiteBuilderBundle\Controller
 */
class CustomerController extends BaseController
{
    /** @var CustomerDispatcher $actionDispatcher */
    protected $actionDispatcher;

    /** @var CustomerData $data */
    protected $data;

    protected $tabItems;

    /** @var SecurityService $securityService */
    protected $securityService;

    /**
     * CustomerController constructor.
     *
     * @param CustomerDispatcher $actionDispatcher
     * @param array $tabItems
     * @param SecurityService $securityService
     */
    public function __construct(
        CustomerDispatcher $actionDispatcher,
        $tabItems,
        SecurityService $securityService
    ) {
        $this->actionDispatcher = $actionDispatcher;
        $this->tabItems = $tabItems;
        $this->securityService = $securityService;
    }

    /**
     * Generate new customer
     *
     * @param Request $request
     * @return \EzSystems\PlatformUIBundle\Http\FormProcessingDoneResponse|null|\Symfony\Component\HttpFoundation\Response
     */
    public function generateAction(Request $request)
    {
        $actionUrl = $this->generateUrl('smileezsb_sb', ['tabItem' => 'dashboard']);
        if (!$this->securityService->checkAuthorization('customergenerate')) {
            return $this->redirectAfterFormPost($actionUrl);
        }

        $form = $this->getForm($request);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dispatchFormAction($this->actionDispatcher, $form, $this->data, array(
                'customerName' => $this->data->customerName,
                'userFirstName' => $this->data->userFirstName,
                'userLastName' => $this->data->userLastName,
                'userEmail' => $this->data->userEmail,
            ));

            if ($response = $this->actionDispatcher->getResponse()) {
                return $response;
            }

            $this->initTask($form);
            $this->initCacheTask();

            return $this->redirectAfterFormPost($actionUrl);
        }

        $this->getErrors($form, 'smileezsb_form_customer');

        $tabItems = $this->tabItems;
        unset($tabItems[0]);
        return $this->render('SmileEzSiteBuilderBundle:sb:index.html.twig', [
            'tab_items' => $tabItems,
            'tab_item_selected' => 'customergenerate',
            'params' => array('customergenerate' => $form->createView()),
            'hasErrors' => true
        ]);
    }

    /**
     * Construct customer generation form
     *
     * @param Request $request
     * @return Form
     */
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

    /**
     * Register customer generation task
     *
     * @param Form $form
     */
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
