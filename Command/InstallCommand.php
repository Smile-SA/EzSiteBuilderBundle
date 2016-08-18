<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Service\InstallService;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class InstallCommand
 *
 * Command used to install Spbuilder prerquisites
 *
 * @package EdgarEz\SiteBuilderBundle\Command
 */
class InstallCommand extends BaseContainerAwareCommand
{
    /** @var int $rootContentLocationID eZ Platform content root location ID */
    protected $rootContentLocationID;

    /** @var int $rootMediaLocationID eZ Platform media root location ID */
    protected $rootMediaLocationID;

    /**
     * @var int $modelsLocationID root location ID for models content
     */
    protected $modelsLocationID;

    /**
     * @var int $customersLocationID root location ID for customers site content
     */
    protected $customersLocationID;

    /**
     * @var int $mediaModelsLocationID media root location ID for models content
     */
    protected $mediaModelsLocationID;

    /**
     * @var int $mediaCustomersLocationID media root location ID for customers site content
     */
    protected $mediaCustomersLocationID;

    /** @var int $userGroupParenttLocationID user group root location ID */
    protected $userGroupParenttLocationID;

    /**
     * @var int $userCreatorsLocationID root locationID for creator users
     */
    protected $userCreatorsLocationID;

    /**
     * @var int $userEditorsLocationID root locationID for editors users
     */
    protected $userEditorsLocationID;

    /** @var string $contentTypeGroup content type */
    protected $contentTypeGroup;

    /**
     * Configure SiteBuilder installation command
     */
    protected function configure()
    {
        $this
            ->setName('edgarez:sitebuilder:install')
            ->setDescription('Install SiteBuilder prerequisites');
    }

    /**
     * Execute SiteBuilder installation command
     *
     * @param InputInterface $input console input
     * @param OutputInterface $output console output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adminID = $this->getContainer()->getParameter('edgar_ez_tools.adminid');
        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->setCurrentUser($repository->getUserService()->loadUser($adminID));

        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the SiteBuilder installation');

        $this->init($input, $output);

        $contentParentLocationID = $this->askContentStructure($input, $output);
        $mediaParentLocationID = $this->askMediaContentStructure($input, $output);
        $userGroupLocationID = $this->askUserStructure($input, $output);

        $this->createContentStructure($output, $contentParentLocationID);
        $this->createMediaContentStructure($output, $mediaParentLocationID);
        $this->createUserStructure($output, $userGroupLocationID);

        $this->createRole();

        /** @var ProjectGenerator $generator */
        $generator = $this->getGenerator();
        $generator->generate(
            $this->modelsLocationID,
            $this->customersLocationID,
            $this->mediaModelsLocationID,
            $this->mediaCustomersLocationID,
            $this->userCreatorsLocationID,
            $this->userEditorsLocationID,
            $this->vendorName,
            $this->dir
        );

        $namespace = $this->vendorName . '\\' . ProjectGenerator::BUNDLE;
        $bundle = $this->vendorName . ProjectGenerator::BUNDLE;
        $this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle);

        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('SiteBuilder Contents and Structure generated', 'bg=blue;fg=white', true),
            ''
        ));
    }

    /**
     * Ask for root location ID where to create customer content structure
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     * @return int content root location ID
     */
    protected function askContentStructure(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var LocationService $locationService */
        $locationService = $repository->getLocationService();

        // Get content root location ID
        $parentLocationID = false;
        $question = new Question($questionHelper->getQuestion('Root Location ID where SiteBuilder content structure will be initialized', $parentLocationID));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateLocationID'
            )
        );

        while (!$parentLocationID) {
            $parentLocationID = $questionHelper->ask($input, $output, $question);

            try {
                $locationService->loadLocation($parentLocationID);
                if (!$parentLocationID || empty($parentLocationID)) {
                    $output->writeln("<error>Parent Location ID is not valid</error>");
                }
            } catch (NotFoundException $e) {
                $output->writeln("<error>No location found with id $parentLocationID</error>");
                $parentLocationID = false;
            }
        }

        $this->rootContentLocationID = $parentLocationID;

        return $parentLocationID;
    }

    /**
     * Create content structure
     *
     * @param OutputInterface $output output console
     * @param int $parentLocationID content root location ID
     */
    protected function createContentStructure(OutputInterface $output, $parentLocationID)
    {
        /** @var InstallService $installService */
        $installService = $this->getContainer()->get('edgar_ez_site_builder.install.service');

        $this->contentTypeGroup = $installService->createContentTypeGroup();
        $installService->createContentTypes($this->contentTypeGroup);
        $contents = $installService->createContents($parentLocationID);

        $this->modelsLocationID = $contents['modelsLocationID'];
        $this->customersLocationID = $contents['customersLocationID'];
    }

    /**
     * Ask for root location ID where to create customer content structure
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     * @return int media root location ID
     */
    protected function askMediaContentStructure(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var LocationService $locationService */
        $locationService = $repository->getLocationService();

        // Get content root location ID
        $parentLocationID = false;
        $question = new Question($questionHelper->getQuestion('Root Location ID where SiteBuilder media content structure will be initialized', $parentLocationID));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateLocationID'
            )
        );

        while (!$parentLocationID) {
            $parentLocationID = $questionHelper->ask($input, $output, $question);

            try {
                $locationService->loadLocation($parentLocationID);
                if (!$parentLocationID || empty($parentLocationID)) {
                    $output->writeln("<error>Parent Location ID is not valid</error>");
                }
            } catch (NotFoundException $e) {
                $output->writeln("<error>No location found with id $parentLocationID</error>");
                $parentLocationID = false;
            }
        }

        $this->rootMediaLocationID = $parentLocationID;

        return $parentLocationID;
    }

    /**
     * Create media structure
     *
     * @param OutputInterface $output
     * @param int $parentLocationID media root location ID
     */
    protected function createMediaContentStructure(OutputInterface $output, $parentLocationID)
    {
        /** @var InstallService $installService */
        $installService = $this->getContainer()->get('edgar_ez_site_builder.install.service');

        $installService->createMediaContentTypes($this->contentTypeGroup);
        $contents = $installService->createMediaContents($parentLocationID);

        $this->mediaModelsLocationID = $contents['modelsLocationID'];
        $this->mediaCustomersLocationID = $contents['customersLocationID'];
    }

    /**
     * Ask for user root location ID
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output outpput console
     * @return int user root location ID
     */
    protected function askUserStructure(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var LocationService $locationService */
        $locationService = $repository->getLocationService();

        // Get user root location ID
        $userGroupParenttLocationID = false;
        $question = new Question($questionHelper->getQuestion('Root User Location ID where SiteBuilder user structure will be initialized: ', $userGroupParenttLocationID));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateLocationID'
            )
        );

        while (!$userGroupParenttLocationID) {
            $userGroupParenttLocationID = $questionHelper->ask($input, $output, $question);

            try {
                $locationService->loadLocation($userGroupParenttLocationID);
                if (!$userGroupParenttLocationID || empty($userGroupParenttLocationID)) {
                    $output->writeln("<error>User Parent Location ID is not valid</error>");
                }
            } catch (NotFoundException $e) {
                $output->writeln("<error>No user location found with id $userGroupParenttLocationID</error>");
                $userGroupParenttLocationID = false;
            }
        }

        return $userGroupParenttLocationID;
    }

    /**
     * Create user structure
     *
     * @param OutputInterface $output output console
     * @param int $userGroupParenttLocationID user root location ID
     */
    protected function createUserStructure(OutputInterface $output, $userGroupParenttLocationID)
    {
        /** @var InstallService $installService */
        $installService = $this->getContainer()->get('edgar_ez_site_builder.install.service');

        /** @var int[] $userGroups */
        $userGroups = $installService->createUserGroups($userGroupParenttLocationID);

        $this->userGroupParenttLocationID = $userGroups['userGroupParenttLocationID'];
        $this->userCreatorsLocationID = $userGroups['userCreatorsLocationID'];
        $this->userEditorsLocationID = $userGroups['userEditorsLocationID'];
    }

    /**
     * Create global role
     */
    protected function createRole()
    {
        /** @var InstallService $installService */
        $installService = $this->getContainer()->get('edgar_ez_site_builder.install.service');

        $locationIDs = array(
            $this->rootContentLocationID,
            $this->rootMediaLocationID,
            $this->customersLocationID,
            $this->mediaCustomersLocationID,
            $this->modelsLocationID,
            $this->mediaModelsLocationID
        );

        $installService->createRole($this->userGroupParenttLocationID, $locationIDs);
    }

    /**
     * Initialize project generator tool
     *
     * @return ProjectGenerator project generator tool
     */
    protected function createGenerator()
    {
        return new ProjectGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('kernel')
        );
    }
}
