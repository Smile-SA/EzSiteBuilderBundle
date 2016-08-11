<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\ToolsBundle\Service\Content;
use EdgarEz\ToolsBundle\Service\ContentType;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\ToolsBundle\Service\ContentTypeGroup;
use EdgarEz\ToolsBundle\Service\Role;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
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
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the SiteBuilder installation');

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
     * Create content types and structure for sitebuilder installation
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function createContentStructure(InputInterface $input, OutputInterface $output)
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

        /**
         * Create site builder content type groups :
         * - SiteBuilder
         */
        /** @var ContentTypeGroup $contentTypeGroupService */
        $contentTypeGroup = $this->getContainer()->get('edgar_ez_tools.contenttypegroup.service');
        $contentTypeGroup = $contentTypeGroup->add('SiteBuilder');
        $output->writeln('ContentTypeGroup <info>SiteBuilder</info> created');

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
                $output->writeln('ContentType <info>' . $contentTypeDefinition['contentTypeName'] . '</info> created</info>');
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
                $contentAdded = $content->add($contentDefinition);
                $contents[] = $contentAdded;
                $output->writeln('Content <info>' . $contentAdded->contentInfo->name . '</info> created');
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

        $content = $this->getContainer()->get('edgar_ez_tools.content.service');

        $userGroupDefinition = Yaml::parse(file_get_contents(__DIR__. '/../Resources/datas/usergrouproot.yml'));
        $userGroupDefinition['parentLocationID'] = $userGroupParenttLocationID;
        /** @var \eZ\Publish\API\Repository\Values\Content\Content $userGroup */
        $userGroup = $content->add($userGroupDefinition);
        $output->writeln('User group root created');

        /** @var \eZ\Publish\API\Repository\Values\Content\Content[] $contents */
        $contents = array();
        $userGroupParenttLocationID = $userGroup->contentInfo->mainLocationId;
        $userGroupDefinitions = glob(__DIR__. '/../Resources/datas/usergroup_*.yml');
        if (is_array($userGroupDefinitions) && count($userGroupDefinitions) > 0) {
            foreach ($userGroupDefinitions as $userGroupDefinition) {
                $userGroupDefinition = Yaml::parse(file_get_contents($userGroupDefinition));
                $userGroupDefinition['parentLocationID'] = $userGroupParenttLocationID;
                /** @var \eZ\Publish\Core\REST\Client\Values\Content\Content $contentAdded */
                $contentAdded = $content->add($userGroupDefinition);
                $contents[] = $contentAdded;
                $output->writeln('User group <info>' . $contentAdded->contentInfo->name . '</info> created');
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
        $question = new Question($questionHelper->getQuestion('Project Vendor name used to construct namespace', null));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateVendorName'
            )
        );

        while (!$vendorName) {
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
