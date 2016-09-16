<?php

namespace Smile\EzSiteBuilderBundle\Command;

use Smile\EzSiteBuilderBundle\Entity\SiteBuilderTask;
use Smile\EzSiteBuilderBundle\Service\Task\TaskInterface;
use eZ\Publish\API\Repository\Repository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class TaskCommand
 * @package Smile\EzSiteBuilderBundle\Command
 */
class TaskCommand extends ContainerAwareCommand
{
    const STATUS_SUBMITTED = 0;
    const STATUS_OK = 1;
    const STATUS_FAIL = 2;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('smile:sitebuilder:task')
            ->setDescription('Execute sitebuilder task');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Registry $dcotrineRegistry */
        $doctrineRegistry = $this->getContainer()->get('doctrine');
        $doctrineManager = $doctrineRegistry->getManager();
        $repository = $doctrineRegistry->getRepository('SmileEzSiteBuilderBundle:SiteBuilderTask');

        $query = $repository->createQueryBuilder('t')
            ->where('t.status = :status')
            ->setParameter('status', self::STATUS_SUBMITTED)
            ->orderBy('t.postedAt', 'ASC')
            ->getQuery();

        /** @var SiteBuilderTask $task */
        $task = $query->setMaxResults(1)->getOneOrNullResult();
        if ($task) {
            $action = $task->getAction();

            try {
                $userID = $task->getUserID();
                /** @var Repository $repository */
                $repository = $this->getContainer()->get('ezpublish.api.repository');
                $repository->setCurrentUser($repository->getUserService()->loadUser($userID));

                if (!isset($action['service'])) {
                    $task->setLogs('task service not identified');
                    return;
                }

                /** @var TaskInterface $taskService */
                $taskService = $this->getContainer()->get(
                    'smile_ez_site_builder.' . $action['service'] . '.task.service'
                );

                if (!isset($action['command'])) {
                    $task->setLogs('task action has no command');
                    return;
                }

                if (!isset($action['parameters'])) {
                    $task->setLogs('task action has no parameters');
                    return;
                }

                $task->setExecutedAt(new \DateTime());
                if (!$taskService->execute(
                    $action['command'],
                    $action['parameters'],
                    $this->getContainer(),
                    $task->getUserID()
                )) {
                    $task->setStatus(self::STATUS_FAIL);
                } else {
                    $task->setStatus(self::STATUS_OK);
                }
                $task->setLogs($taskService->getMessage());
            } catch (\Exception $e) {
                $task->setLogs($taskService->getMessage());
                $task->setStatus(self::STATUS_FAIL);
            }

            $doctrineManager->persist($task);
            $doctrineManager->flush();
        }
    }
}
