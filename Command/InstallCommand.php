<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\ToolsBundle\Service\Content;
use EdgarEz\ToolsBundle\Service\ContentType;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\ToolsBundle\Service\ContentTypeGroup;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class InstallCommand
 *
 * Command used to install Spbuilder prerquisites
 *
 * @package EdgarEz\SiteBuilderBundle\Command
 */
class InstallCommand extends BaseContainerAwareCommand
{
    /**
     * @var int $modelsLocationID root location ID for models content
     */
    protected $modelsLocationID;

    /**
     * @var int $customersLocationID root location ID for customers site content
     */
    protected $customersLocationID;

    /**
     * @var int $userCreatorsLocationID root locationID for creator users
     */
    protected $userCreatorsLocationID;

    /**
     * @var int $userEditorsLocationID root locationID for editors users
     */
    protected $userEditorsLocationID;

    /**
     * @var string $vendorName namespace vendor name where project sitebuilder will be generated
     */
    protected $vendorName;

    /**
     * @var string $dir system directory where bundle would be generated
     */
    protected $dir;

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
        $this->createContentStructure($input, $output);
        $this->createUserStructure($input, $output);
        $this->createProjectBundle($input, $output);

        /** @var ProjectGenerator $generator */
        $generator = $this->getGenerator();
        $generator->generate(
            $this->modelsLocationID,
            $this->customersLocationID,
            $this->userCreatorsLocationID,
            $this->userEditorsLocationID,
            $this->vendorName,
            $this->dir
        );

        $questionHelper = $this->getQuestionHelper();

        $errors = array();
        $runner = $questionHelper->getRunner($output, $errors);
        $namespace = $this->vendorName . '\\' . ProjectGenerator::PROJECT . '\\' . ProjectGenerator::BUNDLE;
        $bundle = $this->vendorName . ProjectGenerator::PROJECT . ProjectGenerator::BUNDLE;
        $runner($this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle));

        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('SiteBuilder Contents and Structure generated', 'bg=blue;fg=white', true),
            ''
        ));
    }

    /**
     * Create content types and structure for sitebuilder installation
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function createContentStructure(InputInterface $input, OutputInterface $output)
    {
        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var LocationService $locationService */
        $locationService = $repository->getLocationService();

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        // Get content root location ID
        $question = new Question('Root Location ID where SiteBuilder content structure will be initialized: ');
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateLocationID'
            )
        );

        $parentLocationID = false;
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

        /**
         * Create site builder content type groups :
         * - SiteBuilder
         */
        /** @var ContentTypeGroup $contentTypeGroupService */
        $contentTypeGroup = $this->getContainer()->get('edgar_ez_tools.contenttypegroup.service');
        $contentTypeGroup = $contentTypeGroup->add('SiteBuilder');
        $output->writeln('<info>ContentTypeGroup SiteBuilder created</info>');

        /**
         * Create site builder content types :
         * - Models root
         * - Model
         * - Customers root
         * - Customer
         */
        /** @var ContentType $contentType */
        $contentType = $this->getContainer()->get('edgar_ez_tools.contenttype.service');
        $contentTypeDefinitions = glob(__DIR__. '/../Resources/datas/contenttype_*.yml');
        if (is_array($contentTypeDefinitions) && count($contentTypeDefinitions) > 0) {
            foreach ($contentTypeDefinitions as $contentTypeDefinition) {
                $contentTypeDefinition = Yaml::parse(file_get_contents($contentTypeDefinition));
                $contentTypeDefinition['contentTypeGroup'] = $contentTypeGroup;
                $contentType->add($contentTypeDefinition);
                $output->writeln('<info>ContentType ' . $contentTypeDefinition['contentTypeName'] . ' created</info>');
            }
        }

        /**
         * Create contents:
         * - Models root
         * - Customers root
         */
        /** @var \eZ\Publish\API\Repository\Values\Content\Content[] $contents */
        $contents = array();
        /** @var Content $content */
        $content = $this->getContainer()->get('edgar_ez_tools.content.service');
        $contentDefinitions = glob(__DIR__. '/../Resources/datas/content_*.yml');
        if (is_array($contentDefinitions) && count($contentDefinitions) > 0) {
            foreach ($contentDefinitions as $contentDefinition) {
                $contentDefinition = Yaml::parse(file_get_contents($contentDefinition));
                $contentDefinition['parentLocationID'] = $parentLocationID;
                $contents[] = $content->add($contentDefinition);
                $output->writeln('<info>Content created</info>');
            }
        }

        foreach ($contents as $content) {
            /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType */
            $contentType = $repository->getContentTypeService()->loadContentType($content->contentInfo->contentTypeId);
            switch ($contentType->identifier) {
                case 'edgar_ez_sb_modelsroot':
                    $this->modelsLocationID = $content->contentInfo->mainLocationId;
                    break;
                case 'edgar_ez_sb_customersroot':
                    $this->customersLocationID = $content->contentInfo->mainLocationId;
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Create user content structure for sitebuilder installation
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output outpput console
     */
    protected function createUserStructure(InputInterface $input, OutputInterface $output)
    {
        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var LocationService $locationService */
        $locationService = $repository->getLocationService();

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        // Get user root location ID
        $question = new Question('Root User Location ID where SiteBuilder user structure will be initialized: ');
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateLocationID'
            )
        );

        $userGroupParenttLocationID = false;
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

        $content = $this->getContainer()->get('edgar_ez_tools.content.service');

        $userGroupDefinition = Yaml::parse(file_get_contents(__DIR__. '/../Resources/datas/usergrouproot.yml'));
        $userGroupDefinition['parentLocationID'] = $userGroupParenttLocationID;
        /** @var \eZ\Publish\API\Repository\Values\Content\Content $userGroup */
        $userGroup = $content->add($userGroupDefinition);
        $output->writeln('<info>User group root created</info>');

        /** @var \eZ\Publish\API\Repository\Values\Content\Content[] $contents */
        $contents = array();
        $userGroupParenttLocationID = $userGroup->contentInfo->mainLocationId;
        $userGroupDefinitions = glob(__DIR__. '/../Resources/datas/usergroup_*.yml');
        if (is_array($userGroupDefinitions) && count($userGroupDefinitions) > 0) {
            foreach ($userGroupDefinitions as $userGroupDefinition) {
                $userGroupDefinition = Yaml::parse(file_get_contents($userGroupDefinition));
                $userGroupDefinition['parentLocationID'] = $userGroupParenttLocationID;
                $contents[] = $content->add($userGroupDefinition);
                $output->writeln('<info>User group created</info>');
            }
        }

        foreach ($contents as $content) {
            /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType */
            $contentType = $repository->getContentTypeService()->loadContentType($content->contentInfo->contentTypeId);
            switch ($contentType->identifier) {
                case 'user_group':
                    if ($content->contentInfo->name == 'Creators') {
                        $this->userCreatorsLocationID = $content->contentInfo->mainLocationId;
                    } else {
                        $this->userEditorsLocationID = $content->contentInfo->mainLocationId;
                    }
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Create sitebuilder porject bundle
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function createProjectBundle(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $vendorName = false;
        while (!$vendorName) {
            $question = new Question($questionHelper->getQuestion('Project Vendor name used to construct namespace', null));
            $question->setValidator(
                array(
                    'EdgarEz\SiteBuilderBundle\Command\Validators',
                    'validateVendorName'
                )
            );
            $vendorName = $questionHelper->ask($input, $output, $question);
        }

        $this->vendorName = $vendorName;

        $dir = false;
        while (!$dir) {
            $dir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

            $output->writeln(array(
                '',
                'The bundle can be generated anywhere. The suggested default directory uses',
                'the standard conventions.',
                '',
            ));

            $question = new Question($questionHelper->getQuestion('Target directory', $dir), $dir);
            $question->setValidator(
                array(
                    'EdgarEz\SiteBuilderBundle\Command\Validators',
                    'validateTargetDir'
                )
            );
            $dir = $questionHelper->ask($input, $output, $question);
        }

        $this->dir = $dir;
    }

    /**
     * Update AppKernel.php adding new sitebuilder project bundle
     *
     * @param QuestionHelper $questionHelper question Helper
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     * @param KernelInterface $kernel symfony Kernel
     * @param $namespace string project namespace
     * @param string $bundle project bundle name
     * @return array message to display at console output
     */
    protected function updateKernel(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output, KernelInterface $kernel, $namespace, $bundle)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Confirm automatic update of your Kernel', 'yes', '?'), true);
            $auto = $questionHelper->ask($input, $output, $question);
        }

        $output->write('Enabling the bundle inside the Kernel: ');
        $manip = new KernelManipulator($kernel);
        try {
            $ret = $auto ? $manip->addBundle($namespace . '\\' . $bundle) : false;

            if (!$ret) {
                $reflected = new \ReflectionObject($kernel);

                return array(
                    sprintf('- Edit <comment>%s</comment>', $reflected->getFilename()),
                    '  and add the following bundle in the <comment>AppKernel::registerBundles()</comment> method:',
                    '',
                    sprintf('    <comment>new %s(),</comment>', $namespace . '\\' . $bundle),
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already defined in <comment>AppKernel::registerBundles()</comment>.', $namespace . '\\' . $bundle),
                '',
            );
        }
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
