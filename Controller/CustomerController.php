<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use EdgarEz\SiteBuilderBundle\Command\TaskCommand;
use EdgarEz\SiteBuilderBundle\Data\Customer\CustomerData;
use EdgarEz\SiteBuilderBundle\Data\Mapper\CustomerMapper;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EdgarEz\SiteBuilderBundle\Form\Type\CustomerType;
use EdgarEz\SiteBuilderBundle\Values\Content\Customer;
use EzSystems\PlatformUIBundle\Controller\Controller;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class CustomerController extends Controller
{
    public function generateAction(Request $request)
    {
        $actionUrl = $this->generateUrl('edgarezsb_sb', ['tabItem' => 'dashboard']);
        $form = $this->getForm($request);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->initTask($form);
            return $this->redirectAfterFormPost($actionUrl);
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/customergenerate.html.twig', [
            'form' => $form->createView(),
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
        $customerData = (new CustomerMapper())->mapToFormData($customer);

        return $this->createForm(new CustomerType(), $customerData);
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

        /** @var Registry $dcotrineRegistry */
        $doctrineRegistry = $this->get('doctrine');
        $doctrineManager = $doctrineRegistry->getManager();

        $task = new SiteBuilderTask();
        $this->submitTask($doctrineManager, $task, $action);
    }

    protected function submitTask(EntityManager $doctrineManager, SiteBuilderTask $task, array $action)
    {
        try {
            $task->setAction($action);
            $task->setStatus(TaskCommand::STATUS_SUBMITTED);
            $task->setPostedAt(new \DateTime());
        } catch (\Exception $e) {
            $task->setLogs('Fail to generate customer');
            $task->setStatus(TaskCommand::STATUS_FAIL);
        } finally {
            /** @var User $user */
            $user = $this->getUser();
            $task->setUserID($user->getAPIUser()->getUserId());

            $doctrineManager->persist($task);
            $doctrineManager->flush();
        }
    }
}
