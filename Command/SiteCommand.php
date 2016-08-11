<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Generator\SiteGenerator;
use EdgarEz\ToolsBundle\Service\Content;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\URLAliasService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class SiteCommand
 *
 * @package EdgarEz\SiteBuilderBundle\Command
 */
class SiteCommand extends BaseContainerAwareCommand
{
    /**
     * @var int $siteLocationID site root location ID
     */
    protected $siteLocationID;

    /**
     * @var string $vendorName namespace vendor name where project sitebuilder will be generated
     */
    protected $vendorName;

    /**
     * @var string $customerName customer name
     */
    protected $customerName;

    /**
     * @var string $mediaCustomerName media customer name
     */
    protected $mediaCustomerName;

    /**
     * @var string $modelName model name
     */
    protected $modelName;

    /**
     * @var string $mediaModelName media model name
     */
    protected $mediaModelName;

    /** @var string $siteName site name */
    protected $siteName;

    /**
     * @var string $excludeUriPrefixes ezplatform path prefix
     */
    protected $excludeUriPrefixes;

    /**
     * @var string $dir system directory where bundle would be generated
     */
    protected $dir;

    /**
     * Configure Customer generator command
     */
    protected function configure()
    {
        $this
            ->setName('edgarez:sitebuilder:site:generate')
            ->setDescription('Generate SiteBuilder Site (Content Structure and Bundle)');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'SiteBuilder Site initialization');

        $this->createSiteContent($input, $output);
        $this->createMediaSiteContent($input, $output);
        $this->createSiteBundle($input, $output);

        /** @var SiteGenerator $generator */
        $generator = $this->getGenerator();
        $generator->generate(
            $this->siteLocationID,
            $this->vendorName,
            $this->customerName,
            $this->modelName,
            $this->siteName,
            $this->excludeUriPrefixes,
            $this->dir
        );

        $namespace = $this->vendorName . '\\' . ProjectGenerator::CUSTOMERS . '\\' . $this->customerName . '\\' . CustomerGenerator::SITES . '\\' . $this->siteName . 'Bundle';
        $bundle = $this->vendorName . ProjectGenerator::CUSTOMERS . $this->customerName . CustomerGenerator::SITES . $this->siteName . 'Bundle';
        $this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle);

        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('SiteBuilder Contents and Structure generated', 'bg=blue;fg=white', true),
            ''
        ));
    }

    /**
     * Choose and copy Model content structure to customer content tree
     *
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function createSiteContent(InputInterface $input, OutputInterface $output)
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

        $siteName = false;
        $question = new Question($questionHelper->getQuestion('Site name you want to create', null));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateVendorName'
            )
        );

        while (!$siteName) {
            $siteName = $questionHelper->ask($input, $output, $question);
        }

        $this->siteName = $siteName;

        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var LocationService $locationService */
        $locationService = $repository->getLocationService();
        /** @var URLAliasService $urlAliasService */
        $urlAliasService = $repository->getURLAliasService();

        // Get customer content root location ID
        $customerLocationID = false;
        $question = new Question($questionHelper->getQuestion('Customer location ID where your site content would be generated', $customerLocationID));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateLocationID'
            )
        );

        while (!$customerLocationID) {
            $customerLocationID = $questionHelper->ask($input, $output, $question);

            try {
                $locationService->loadLocation($customerLocationID);
                if (!$customerLocationID || empty($customerLocationID)) {
                    $output->writeln("<error>Customer Location ID is not valid</error>");
                }
            } catch (NotFoundException $e) {
                $output->writeln("<error>No location found with id $customerLocationID</error>");
                $customerLocationID = false;
            }
        }

        // Get model content root location ID
        $modelLocationID = false;
        $question = new Question($questionHelper->getQuestion('Model location ID you want to use to generate ste content', $modelLocationID));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateLocationID'
            )
        );

        while (!$modelLocationID) {
            $modelLocationID = $questionHelper->ask($input, $output, $question);

            try {
                $locationService->loadLocation($modelLocationID);
                if (!$modelLocationID || empty($modelLocationID)) {
                    $output->writeln("<error>Model Location ID is not valid</error>");
                }
            } catch (NotFoundException $e) {
                $output->writeln("<error>No location found with id $modelLocationID</error>");
                $modelLocationID = false;
            }
        }

        $customerLocation = $locationService->loadLocation($customerLocationID);
        $this->customerName = $customerLocation->getContentInfo()->name;
        $modelLocation = $locationService->loadLocation($modelLocationID);
        $this->modelName = $modelLocation->getContentInfo()->name;

        try {
            // Copy model content subtree to customer tree
            /** @var Content $content */
            $content = $this->getContainer()->get('edgar_ez_tools.content.service');
            $siteLocationID = $content->copySubtree($modelLocationID, $customerLocationID, $this->siteName);

            $this->siteLocationID = $siteLocationID;
            $newLocation = $locationService->loadLocation($this->siteLocationID);

            $contentPath = $urlAliasService->reverseLookup($newLocation, $newLocation->getContentInfo()->mainLanguageCode)->path;
            $this->excludeUriPrefixes = trim($contentPath, '/') . '/';
        } catch (InvalidArgumentException $e) {
            $output->writeln("<error>Invalid argument [$modelLocationID] or [$customerLocationID]</error>");
        }
    }

    /**
     * Choose and copy Media Model content structure to customer media content tree
     *
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function createMediaSiteContent(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var LocationService $locationService */
        $locationService = $repository->getLocationService();

        // Get customer media content root location ID
        $mediaCustomerLocationID = false;
        $question = new Question($questionHelper->getQuestion('Customer media location ID where your site media content would be generated', $mediaCustomerLocationID));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateLocationID'
            )
        );

        while (!$mediaCustomerLocationID) {
            $mediaCustomerLocationID = $questionHelper->ask($input, $output, $question);

            try {
                $locationService->loadLocation($mediaCustomerLocationID);
                if (!$mediaCustomerLocationID || empty($mediaCustomerLocationID)) {
                    $output->writeln("<error>Customer Location ID is not valid</error>");
                }
            } catch (NotFoundException $e) {
                $output->writeln("<error>No location found with id $mediaCustomerLocationID</error>");
                $mediaCustomerLocationID = false;
            }
        }

        // Get model content root location ID
        $mediaModelLocationID = false;
        $question = new Question($questionHelper->getQuestion('Model location ID yout want to use to generate ste content', $mediaModelLocationID));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateLocationID'
            )
        );

        while (!$mediaModelLocationID) {
            $mediaModelLocationID = $questionHelper->ask($input, $output, $question);

            try {
                $locationService->loadLocation($mediaModelLocationID);
                if (!$mediaModelLocationID || empty($mediaModelLocationID)) {
                    $output->writeln("<error>Model Location ID is not valid</error>");
                }
            } catch (NotFoundException $e) {
                $output->writeln("<error>No location found with id $mediaModelLocationID</error>");
                $mediaModelLocationID = false;
            }
        }

        $mediaCustomerLocation = $locationService->loadLocation($mediaCustomerLocationID);
        $this->mediaCustomerName = $mediaCustomerLocation->getContentInfo()->name;
        $mediaModelLocation = $locationService->loadLocation($mediaModelLocationID);
        $this->mediaModelName = $mediaModelLocation->getContentInfo()->name;

        try {
            // Copy model content subtree to customer tree
            /** @var Content $content */
            $content = $this->getContainer()->get('edgar_ez_tools.content.service');
            $content->copySubtree($mediaModelLocationID, $mediaCustomerLocationID, $this->siteName);
        } catch (InvalidArgumentException $e) {
            $output->writeln("<error>Invalid argument [$mediaModelLocationID] or [$mediaCustomerLocationID]</error>");
        }
    }

    /**
     * Retrieve informations to generate new Customer site bundle
     *
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function createSiteBundle(InputInterface $input, OutputInterface $output)
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
     * Initialize customer generator tool
     *
     * @return SiteGenerator customer generator tool
     */
    protected function createGenerator()
    {
        return new SiteGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('kernel')
        );
    }
}