<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Generator\ModelGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\ToolsBundle\Service\Content;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\URLAliasService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ModelCommand
 * @package EdgarEz\SiteBuilderBundle\Command
 */
class ModelCommand extends BaseContainerAwareCommand
{
    /**
     * @var string $vendorName model bundle vendor name used to construct namespace
     */
    protected $vendorName;

    /**
     * @var string $modelName model bundle name used to construct namespace
     */
    protected $modelName;

    /**
     * @var int $modelLocationID root location ID for model content
     */
    protected $modelLocationID;

    /**
     * @var string $dir system directory where model bundle would be generated
     */
    protected $dir;

    /**
     * @var string $excludeUriPrefixes ezplatform settings for model bundle siteaccess configuration
     */
    protected $excludeUriPrefixes;

    /**
     * Configure Model generator command
     */
    protected function configure()
    {
        $this
            ->setName('edgarez:sitebuilder:model:generate')
            ->setDescription('Generate SiteBuilder Model (Content Structure and Bundle)');
    }

    /**
     * Execute Model generator command
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'SiteBuilder model initialization');

        $this->createModelContent($input, $output);
        $this->createModelBundle($input, $output);

        /** @var ModelGenerator $generator */
        $generator = $this->getGenerator();
        $generator->generate(
            $this->vendorName,
            $this->modelName,
            $this->modelLocationID,
            $this->excludeUriPrefixes,
            $this->dir
        );

        $namespace = $this->vendorName . '\\' . ProjectGenerator::MODELS . '\\' . $this->modelName . 'Bundle';
        $bundle = $this->vendorName . ProjectGenerator::MODELS . $this->modelName . 'Bundle';
        $this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle);

        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('New model content and bundle generated', 'bg=blue;fg=white', true),
            ''
        ));
    }

    /**
     * Create model content
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function createModelContent(InputInterface $input, OutputInterface $output)
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

        $modelName = false;
        $question = new Question($questionHelper->getQuestion('Model name used to construct namespace', null));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateModelName'
            )
        );

        while (!$modelName) {
            $modelName = $questionHelper->ask($input, $output, $question);
        }

        $this->modelName = $modelName;

        $basename = $this->vendorName . ProjectGenerator::MAIN ;

        /** @var Content $content */
        $content = $this->getContainer()->get('edgar_ez_tools.content.service');
        $contentDefinition = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/datas/modelcontent.yml'));
        $contentDefinition['parentLocationID'] = $this->getContainer()->getParameter(Container::underscore($basename) . '.default.models_location_id');
        $contentDefinition['fields']['title']['value'] = $this->modelName;
        $contentAdded = $content->add($contentDefinition);

        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var URLAliasService $urlAliasService */
        $urlAliasService = $repository->getURLAliasService();

        /** @var LocationService $locationService */
        $locationService = $repository->getLocationService();

        $contentLocation = $locationService->loadLocation($contentAdded->contentInfo->mainLocationId);
        $contentPath = $urlAliasService->reverseLookup($contentLocation, $contentAdded->contentInfo->mainLanguageCode)->path;
        $this->excludeUriPrefixes = trim($contentPath, '/') . '/';

        $this->modelLocationID = $contentAdded->contentInfo->mainLocationId;
    }

    /**
     * Create model bundle
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function createModelBundle(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

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
     * Initialize model generator tool
     *
     * @return ModelGenerator model generator tool
     */
    protected function createGenerator()
    {
        return new ModelGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('kernel')
        );
    }
}
