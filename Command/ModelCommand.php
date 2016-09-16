<?php

namespace Smile\EzSiteBuilderBundle\Command;

use Smile\EzSiteBuilderBundle\Generator\ModelGenerator;
use Smile\EzSiteBuilderBundle\Generator\ProjectGenerator;
use Smile\EzSiteBuilderBundle\Service\ModelService;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class ModelCommand
 * @package Smile\EzSiteBuilderBundle\Command
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
            ->setName('smile:sitebuilder:model:generate')
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
        $adminID = $this->getContainer()->getParameter('smile_ez_tools.adminid');
        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->setCurrentUser($repository->getUserService()->loadUser($adminID));

        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'SiteBuilder model initialization');

        $this->getVendorNameDir();

        $this->askModelContent($input, $output);

        /** @var ModelService $modelService */
        $modelService = $this->getContainer()->get('smile_ez_site_builder.model.service');
        /** @var LanguageService $languageService */
        $languageService = $repository->getContentLanguageService();

        try {
            $basename = ProjectGenerator::MAIN ;

            $languageCode = $languageService->getDefaultLanguageCode();

            $modelsLocationID = $this->getContainer()->getParameter(
                'smileez_sb.' . strtolower($basename) . '.default.models_location_id'
            );
            $returnValue = $modelService->createModelContent($modelsLocationID, $this->modelName, $languageCode);
            $this->excludeUriPrefixes = $returnValue['excludeUriPrefixes'];
            $this->modelLocationID = $returnValue['modelLocationID'];

            $mediaModelsLocationID = $this->getContainer()->getParameter(
                'smileez_sb.' . strtolower($basename) . '.default.media_models_location_id'
            );
            $returnValue = $modelService->createMediaModelContent(
                $mediaModelsLocationID,
                $this->modelName,
                $languageCode
            );
            $this->mediaModelLocationID = $returnValue['mediaModelLocationID'];


            $modelService->updateGlobalRole($this->modelLocationID, $this->mediaModelLocationID);

            /** @var ModelGenerator $generator */
            $generator = $this->getGenerator();
            $generator->generate(
                $languageCode,
                $this->vendorName,
                $this->modelName,
                $this->modelLocationID,
                $this->mediaModelLocationID,
                $this->excludeUriPrefixes,
                $this->getContainer()->getParameter('smile_ez_site_builder.host'),
                $this->dir
            );

            $namespace = $this->vendorName . '\\' . ProjectGenerator::MODELS . '\\' . $this->modelName . 'Bundle';
            $bundle = $this->vendorName . ProjectGenerator::MODELS . $this->modelName . 'Bundle';
            $this->updateKernel(
                $questionHelper,
                $input,
                $output,
                $this->getContainer()->get('kernel'),
                $namespace,
                $bundle
            );

            $output->writeln(array(
                '',
                $this->getHelper('formatter')->formatBlock(
                    'New model content and bundle generated',
                    'bg=blue;fg=white',
                    true
                ),
                ''
            ));
        } catch (\RuntimeException $e) {
            $output->write('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * Ask for model name
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function askModelContent(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        /** @var ModelService $modelService */
        $modelService = $this->getContainer()->get('smile_ez_site_builder.model.service');

        $modelName = false;
        $question = new Question($questionHelper->getQuestion('Model name used to construct namespace', null));
        $question->setValidator(
            array(
                'Smile\EzSiteBuilderBundle\Command\Validators',
                'validateModelName'
            )
        );

        while (!$modelName) {
            $modelName = $questionHelper->ask($input, $output, $question);
            $exists = $modelService->exists($modelName, $this->vendorName, $this->dir);
            if ($exists) {
                $output->writeln('<error>This model already exists with this name</error>');
                $modelName = false;
            }
        }

        $this->modelName = $modelName;
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
