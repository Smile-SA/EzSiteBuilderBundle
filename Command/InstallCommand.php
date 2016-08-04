<?php
/**
 * Created by PhpStorm.
 * User: emdro
 * Date: 02/08/2016
 * Time: 14:36
 */

namespace EdgarEz\SiteBuilderBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InstallCommand extends ContainerAwareCommand
{
    /**
     * Configure SiteBuilder installation command
     */
    protected function configure()
    {
        $this
            ->setName('edgar_ez:sitebuilder:install')
            ->setDescription('SiteBuilder');
    }

    /**
     * Execute SiteBuilder installation command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $questionHelper QuestionHelper */
        $questionHelper = $this->getHelper('question');

        $question = new Question('Root Location ID where SiteBuilder content structure will be initialized: ');
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateRootLocationID'
            )
        );

        $rootLocationID = $questionHelper->ask($input, $output, $question);
    }
}