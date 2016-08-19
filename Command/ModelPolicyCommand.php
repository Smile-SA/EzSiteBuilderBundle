<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Service\ModelService;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class ModelPolicyCommand
 *
 * @package EdgarEz\SiteBuilderBundle\Command
 */
class ModelPolicyCommand extends BaseContainerAwareCommand
{
    /** @var string $modelName model name */
    protected $modelName;

    /**
     * Configure Customer generator command
     */
    protected function configure()
    {
        $this
            ->setName('edgarez:sitebuilder:model:policy')
            ->setDescription('Manage SiteBuilder Model limitation policies');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adminID = $this->getContainer()->getParameter('edgar_ez_tools.adminid');
        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->setCurrentUser($repository->getUserService()->loadUser($adminID));

        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'SiteBuilder Model limitation policies managment for user creator');

        $this->getVendorNameDir();

        $this->askModelName($input, $output);

        $this->addSiteaccessLimitation($this->modelName);

        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock(
                array(
                    'Siteaccess Limitation are set for your customer creators roles'
                ),
                'bg=blue;fg=white',
                true
            ),
            ''
        ));
    }

    /**
     * Ask for model name
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function askModelName(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $modelName = false;
        $question = new Question($questionHelper->getQuestion('Model name used to construct namespace', null));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateVendorName'
            )
        );

        while (!$modelName) {
            $modelName = $questionHelper->ask($input, $output, $question);
        }

        $this->modelName = $modelName;
    }

    /**
     * Add siteaccess limitation to user/login foreach user creator roles
     *
     * @param string $modelName model name
     */
    protected function addSiteaccessLimitation($modelName)
    {
        /** @var ModelService $modelService */
        $modelService = $this->getContainer()->get('edgar_ez_site_builder.model.service');

        $modelService->addSiteaccessLimitation(Container::underscore($this->vendorName . $modelName));
    }

    /**
     * Initialize generator tool
     *
     * @return null
     */
    protected function createGenerator()
    {
        return null;
    }
}
