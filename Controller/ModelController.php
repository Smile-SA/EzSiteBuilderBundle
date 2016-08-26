<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use EdgarEz\SiteBuilderBundle\Command\TaskCommand;
use EdgarEz\SiteBuilderBundle\Data\Mapper\ModelMapper;
use EdgarEz\SiteBuilderBundle\Data\Model\ModelData;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EdgarEz\SiteBuilderBundle\Form\Type\ModelType;
use EdgarEz\SiteBuilderBundle\Values\Content\Model;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use EzSystems\PlatformUIBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class ModelController extends Controller
{
    public function generateAction(Request $request)
    {
        $actionUrl = $this->generateUrl('edgarezsb_sb', ['tabItem' => 'dashboard']);
        $form = $this->getForm($request);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->initTask($form);
            $this->initPolicyTask($form);
            return $this->redirectAfterFormPost($actionUrl);
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/modelgenerate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    protected function getForm(Request $request)
    {
        $model = new Model([
            'modelName' => '',
        ]);
        $modelData = (new ModelMapper())->mapToFormData($model);

        return $this->createForm(new ModelType(), $modelData);
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

        /** @var Registry $dcotrineRegistry */
        $doctrineRegistry = $this->get('doctrine');
        $doctrineManager = $doctrineRegistry->getManager();

        $task = new SiteBuilderTask();
        $this->submitTask($doctrineManager, $task, $action);
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

        /** @var Registry $dcotrineRegistry */
        $doctrineRegistry = $this->get('doctrine');
        $doctrineManager = $doctrineRegistry->getManager();

        $task = new SiteBuilderTask();
        $postedAt = new \DateTime();
        $postedAt->modify('+5 minutes');
        $this->submitTask($doctrineManager, $task, $action, $postedAt);
    }

    protected function submitTask(EntityManager $doctrineManager, SiteBuilderTask $task, array $action, \DateTime $postedAt = null)
    {
        $postedAt = $postedAt ? $postedAt : new \DateTime();
        try {
            $task->setAction($action);
            $task->setStatus(TaskCommand::STATUS_SUBMITTED);
            $task->setPostedAt($postedAt);
        } catch (\Exception $e) {
            $task->setLogs('Fail to generate task');
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
