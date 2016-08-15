<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Command\Task\BaseTask;
use EdgarEz\SiteBuilderBundle\Command\Task\Task;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EdgarEz\SiteBuilderBundle\Service\Task\TaskInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

class TaskCommand extends ContainerAwareCommand
{
    const STATE_SUBMITTED = 0;
    const STATE_OK = 1;
    const STATE_FAIL = 2;

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
            ->where('t.state = :state')
            ->setParameter('state', self::STATE_SUBMITTED)
            ->orderBy('t.postedAt', 'ASC')
            ->getQuery();

        /** @var SiteBuilderTask $task */
        $task = $query->setMaxResults(1)->getOneOrNullResult();
        if ($task) {
            $action = $task->getAction();

            if (!isset($action['command'])) {
                $output->writeln('task action has no command');
                return;
            }

            if (!isset($action['parameters'])) {
                $output->writeln('task action has no parameters');
                return;
            }

            /** @var TaskInterface $taskService */
            $taskService = $this->getContainer()->get('edgar_ez_site_builder.' . $action['command'] . '.task.service');
            $result = $taskService->execute($action['parameters']);

            if (!$result) {
                $output->writeln('<error>error : ' . $taskService->getMessage().'</error>');
                $task->setState(self::STATE_FAIL);
            } else {
                $task->setState(self::STATE_OK);
            }

            $task->setExecutedAt(time());
            $doctrineManager->persist($task);
            $doctrineManager->flush();
        } else {
            $output->writeln('no task to execute');
        }
    }
}
