<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EdgarEz\SiteBuilderBundle\Service\Task\TaskInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class TaskCommand
 * @package EdgarEz\SiteBuilderBundle\Command
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
            ->setName('edgarez:sitebuilder:task')
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
        $repository = $doctrineRegistry->getRepository('EdgarEzSiteBuilderBundle:SiteBuilderTask');

        $query = $repository->createQueryBuilder('t')
            ->where('t.status = :status')
            ->setParameter('status', self::STATUS_SUBMITTED)
            ->orderBy('t.postedAt', 'ASC')
            ->getQuery();

        /** @var SiteBuilderTask $task */
        $task = $query->setMaxResults(1)->getOneOrNullResult();
        if ($task) {
            $action = $task->getAction();

            if (!isset($action['service'])) {
                $output->writeln('task service not identified');
                return;
            }

            if (!isset($action['command'])) {
                $output->writeln('task action has no command');
                return;
            }

            if (!isset($action['parameters'])) {
                $output->writeln('task action has no parameters');
                return;
            }

            /** @var TaskInterface $taskService */
            $taskService = $this->getContainer()->get('edgar_ez_site_builder.' . $action['service'] . '.task.service');
            $result = $taskService->execute($action['command'], $action['parameters']);

            if (!$result) {
                $task->setLogs($task->getLogs());
                $output->writeln('<error>error : ' . $taskService->getMessage().'</error>');
                $task->setStatus(self::STATUS_FAIL);
            } else {
                $task->setStatus(self::STATUS_OK);
            }

            $task->setExecutedAt(new \DateTime());
            $doctrineManager->persist($task);
            $doctrineManager->flush();
        } else {
            $output->writeln('no task to execute');
        }
    }
}
