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

class InstallCommand extends BaseContainerAwareCommand
{
    protected $modelsLocationID;
    protected $customersLocationID;
    protected $userCreatorsLocationID;
    protected $userEditorsLocationID;
    protected $vendorName;
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createContentStructure($input, $output);
        $this->createUserStructure($input, $output);
        $this->createProjectBundle($input, $output);

        /** @var $generator ProjectGenerator */
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
        // register the bundle in the Kernel class
        $runner($this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle));

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('SiteBuilder Contents and Structure generated', 'bg=blue;fg=white', true),
            ''
        ));
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
        /** @var $contents \eZ\Publish\API\Repository\Values\Content\Content[] */
        $contents = array();
        /** @var $content Content */
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
            /** @var $contentType \eZ\Publish\API\Repository\Values\ContentType\ContentType */
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

        /** @var $contents \eZ\Publish\API\Repository\Values\Content\Content[] */
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
            /** @var $contentType \eZ\Publish\API\Repository\Values\ContentType\ContentType */
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

    protected function checkAutoloader(OutputInterface $output, $namespace, $bundle, $dir)
    {
        $output->write('Checking that the Project SiteBuilder bundle is autoloaded: ');
        if (!class_exists($namespace . '\\' . $bundle)) {
            return array(
                '- Edit the <comment>composer.json</comment> file and register the bundle',
                '  namespace in the "autoload" section:',
                '',
            );
        }
    }

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

    protected function createGenerator()
    {
        return new ProjectGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('kernel')
        );
    }
}
