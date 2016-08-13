<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Generator\ModelGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Service\ModelService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class ModelCommand
 * @package EdgarEz\SiteBuilderBundle\Command
 */
class ModelCommand extends BaseContainerAwareCommand
{
    /**
     * @var string $modelName model bundle name used to construct namespace
     */
    protected $modelName;

    /**
     * @var int $modelLocationID root location ID for model content
     */
    protected $modelLocationID;

    /**
     * @var int $mediaModelLocationID media root location ID for model content
     */
    protected $mediaModelLocationID;

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

        $this->init($input, $output);

        $this->createModelContent($input, $output);
        $this->createMediaModelContent($input, $output);

        /** @var ModelGenerator $generator */
        $generator = $this->getGenerator();
        $generator->generate(
            $this->vendorName,
            $this->modelName,
            $this->modelLocationID,
            $this->mediaModelLocationID,
            $this->excludeUriPrefixes,
            $this->getContainer()->getParameter('edgar_ez_site_builder.host'),
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

        /** @var ModelService $modelService */
        $modelService = $this->getContainer()->get('edgar_ez_site_builder.model.service');
        $modelsLocationID = $this->getContainer()->getParameter(Container::underscore($basename) . '.default.models_location_id');

        $content = $modelService->createModelContent($modelsLocationID, $this->modelName);
        $this->excludeUriPrefixes = $content['excludeUriPrefixes'];
        $this->modelLocationID = $content['modelLocationID'];
    }

    /**
     * Create media model content
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function createMediaModelContent(InputInterface $input, OutputInterface $output)
    {
        $basename = $this->vendorName . ProjectGenerator::MAIN;

        /** @var ModelService $modelService */
        $modelService = $this->getContainer()->get('edgar_ez_site_builder.model.service');
        $mediaModelsLocationID = $this->getContainer()->getParameter(Container::underscore($basename) . '.default.media_models_location_id');

        $this->mediaModelLocationID = $modelService->createMediaModelContent($mediaModelsLocationID, $this->modelName);
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
