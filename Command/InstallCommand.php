<?php
/**
 * Created by PhpStorm.
 * User: emdro
 * Date: 02/08/2016
 * Time: 14:36
 */

namespace EdgarEz\SiteBuilderBundle\Command;


use EdgarEz\ToolsBundle\Service\Content;
use EdgarEz\ToolsBundle\Service\ContentType;
use EdgarEz\ToolsBundle\Service\ContentTypeGroup;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class InstallCommand extends ContainerAwareCommand
{
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createContentStructure($input, $output);
        $this->createUserStructure($input, $output);
    }

    protected function createContentStructure(InputInterface $input, OutputInterface $output)
    {
        /** @var $repository Repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var $locationService LocationService */
        $locationService = $repository->getLocationService();

        /** @var $questionHelper QuestionHelper */
        $questionHelper = $this->getHelper('question');

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
        /** @var $contentTypeGroupService ContentTypeGroup */
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
        /** @var $contentType ContentType */
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
        /** @var $content Content */
        $content = $this->getContainer()->get('edgar_ez_tools.content.service');
        $contentDefinitions = glob(__DIR__. '/../Resources/datas/content_*.yml');
        if (is_array($contentDefinitions) && count($contentDefinitions) > 0) {
            foreach ($contentDefinitions as $contentDefinition) {
                $contentDefinition = Yaml::parse(file_get_contents($contentDefinition));
                $contentDefinition['parentLocationID'] = $parentLocationID;
                $content->add($contentDefinition);
                $output->writeln('<info>Content created</info>');
            }
        }
    }

    protected function createUserStructure(InputInterface $input, OutputInterface $output)
    {
        /** @var $repository Repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var $locationService LocationService */
        $locationService = $repository->getLocationService();

        /** @var $questionHelper QuestionHelper */
        $questionHelper = $this->getHelper('question');

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
        /** @var $userGroup \eZ\Publish\API\Repository\Values\Content\Content */
        $userGroup = $content->add($userGroupDefinition);
        $output->writeln('<info>User group root created</info>');

        $userGroupDefinitions = glob(__DIR__. '/../Resources/datas/usergroup_*.yml');
        if (is_array($userGroupDefinitions) && count($userGroupDefinitions) > 0) {
            foreach ($userGroupDefinitions as $userGroupDefinition) {
                $userGroupDefinition = Yaml::parse(file_get_contents($userGroupDefinition));
                $userGroupDefinition['parentLocationID'] = $userGroup->contentInfo->mainLocationId;
                $content->add($userGroupDefinition);
                $output->writeln('<info>User group created</info>');
            }
        }
    }
}