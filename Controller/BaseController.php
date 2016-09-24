<?php

namespace Smile\EzSiteBuilderBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Smile\EzSiteBuilderBundle\Command\TaskCommand;
use Smile\EzSiteBuilderBundle\Entity\SiteBuilderTask;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use EzSystems\PlatformUIBundle\Controller\Controller;
use EzSystems\RepositoryForms\Form\ActionDispatcher\ActionDispatcherInterface;
use Symfony\Component\Form\Form;

/**
 * Class BaseController
 * @package Smile\EzSiteBuilderBundle\Controller
 */
abstract class BaseController extends Controller
{
    /**
     * Register task to be executed after x minutes
     *
     * @param array $action action and action parameters
     * @param int $minutes set action date time delay execution
     */
    protected function submitFuturTask(array $action, $minutes = 1)
    {
        $minutes = ($minutes > 1) ? '+' . $minutes . ' minutes' : '+' . $minutes . ' minutes';
        $task = new SiteBuilderTask();
        $postedAt = new \DateTime();
        $postedAt->modify($minutes);
        $this->submitTask($task, $action, $postedAt);
    }

    /**
     * Register task to be executed
     *
     * @param SiteBuilderTask $task task registrar
     * @param array $action action and action parameters
     * @param \DateTime|null $postedAt set action date time execution
     */
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

    /**
     * Notify interface form action error
     *
     * @param Form $form form
     * @param $formID form identifier
     */
    protected function getErrors(Form $form, $formID)
    {
        foreach ($form->getErrors(true) as $error) {
            $this->notifyErrorPlural(
                $error->getMessageTemplate(),
                $error->getMessagePluralization(),
                $error->getMessageParameters(),
                $formID
            );
        }
    }

    /**
     * Dispatch form action
     *
     * @param ActionDispatcherInterface $actionDispatcher form actionDispatcher
     * @param Form $form form
     * @param ValueObject $data form datas
     * @param array $options form options
     */
    protected function dispatchFormAction(
        ActionDispatcherInterface $actionDispatcher,
        Form $form,
        ValueObject $data,
        array $options
    ) {
        $actionDispatcher->dispatchFormAction(
            $form,
            $data,
            $form->getClickedButton() ? $form->getClickedButton()->getName() : null,
            $options
        );
    }

    /**
     * Register cache:clear task to be executed
     *
     * @param int $minutes set date time delay execution
     */
    protected function initCacheTask($minutes = 1)
    {
        $action = array(
            'service'    => 'cache',
            'command'    => 'clear',
            'parameters' => array()
        );

        $this->submitFuturTask($action, $minutes);
    }
}
