<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use EdgarEz\SiteBuilderBundle\Command\TaskCommand;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use EzSystems\PlatformUIBundle\Controller\Controller;

abstract class BaseController extends Controller
{
    protected function submitFuturTask($action)
    {
        $task = new SiteBuilderTask();
        $postedAt = new \DateTime();
        $postedAt->modify('+5 minutes');
        $this->submitTask($task, $action, $postedAt);
    }

    protected function submitTask(SiteBuilderTask $task, array $action, \DateTime $postedAt = null)
    {
        /** @var Registry $dcotrineRegistry */
        $doctrineRegistry = $this->get('doctrine');
        $doctrineManager = $doctrineRegistry->getManager();

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
