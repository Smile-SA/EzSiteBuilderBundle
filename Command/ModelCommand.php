<?php
namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Generator\ModelGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\ToolsBundle\Service\Content;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\URLAliasService;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class ModelCommand extends BaseContainerAwareCommand
{
    protected $vendorName;
    protected $modelName;
    protected $modelLocationID;
    protected $dir;
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createModelContent($input, $output);
        $this->createModelBundle($input, $output);

        /** @var $generator ModelGenerator */
        $generator = $this->getGenerator();
        $generator->generate(
            $this->vendorName,
            $this->modelName,
            $this->modelLocationID,
            $this->excludeUriPrefixes,
            $this->dir
        );

        $questionHelper = $this->getQuestionHelper();

        $errors = array();
        $runner = $questionHelper->getRunner($output, $errors);
        $namespace = $this->vendorName . '\\' . ProjectGenerator::PROJECT . '\\' . ProjectGenerator::MODELS . '\\' . $this->modelName . 'Bundle';
        $bundle = $this->vendorName . ProjectGenerator::PROJECT . ProjectGenerator::MODELS . $this->modelName . 'Bundle';
        // register the bundle in the Kernel class
        $runner($this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle));

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('New model conent and bundle generated', 'bg=blue;fg=white', true),
            ''
        ));
    }

    protected function createModelContent(InputInterface $input, OutputInterface $output)
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

        $modelName = false;
        while (!$modelName) {
            $question = new Question($questionHelper->getQuestion('Modelr name used to construct namespace', null));
            $question->setValidator(
                array(
                    'EdgarEz\SiteBuilderBundle\Command\Validators',
                    'validateModelName'
                )
            );
            $modelName = $questionHelper->ask($input, $output, $question);
        }

        $this->modelName = $modelName;

        $basename = $this->vendorName . ProjectGenerator::PROJECT . ProjectGenerator::MAIN ;

        /** @var $content Content */
        $content = $this->getContainer()->get('edgar_ez_tools.content.service');
        $contentDefinition = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/datas/modelcontent.yml'));
        $contentDefinition['parentLocationID'] = $this->getContainer()->getParameter(Container::underscore($basename) . '.default.models_location_id');
        $contentDefinition['fields']['title']['value'] = $this->modelName;
        $contentAdded = $content->add($contentDefinition);

        /** @var $repository Repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        /** @var $urlAliasService URLAliasService */
        $urlAliasService = $repository->getURLAliasService();

        /** @var $locationService LocationService */
        $locationService = $repository->getLocationService();

        $contentLocation = $locationService->loadLocation($contentAdded->contentInfo->mainLocationId);
        $contentPath = $urlAliasService->reverseLookup($contentLocation, $contentAdded->contentInfo->mainLanguageCode)->path;
        $this->excludeUriPrefixes = trim($contentPath, '/') . '/';

        $this->modelLocationID = $contentAdded->contentInfo->mainLocationId;
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

    protected function createGenerator()
    {
        return new ModelGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('kernel')
        );
    }
}