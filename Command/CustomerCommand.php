<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\ToolsBundle\Service\Content;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class CustomerCommand extends BaseContainerAwareCommand
{
    /**
     * @var int $customerLocationID customer content location ID
     */
    protected $customerLocationID;

    /**
     * @var string $vendorName namespace vendor name where project sitebuilder will be generated
     */
    protected $vendorName;

    /**
     * @var string $customerName customer name
     */
    protected $customerName;

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
            ->setName('edgarez:sitebuilder:customer:generate')
            ->setDescription('Generate SiteBuilder Customer (Content Structure and Bundle)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createContentStructure($input, $output);
        $this->createCustomerBundle($input, $output);

        /** @var CustomerGenerator $generator */
        $generator = $this->getGenerator();
        $generator->generate(
            $this->customerLocationID,
            $this->vendorName,
            $this->customerName,
            $this->dir
        );

        $questionHelper = $this->getQuestionHelper();

        $errors = array();
        $runner = $questionHelper->getRunner($output, $errors);
        $namespace = $this->vendorName . '\\' . ProjectGenerator::CUSTOMERS . '\\' . $this->customerName . '\\' . CustomerGenerator::BUNDLE ;
        $bundle = $this->vendorName . ProjectGenerator::CUSTOMERS . $this->customerName . CustomerGenerator::BUNDLE;
        $runner($this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle));

        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('SiteBuilder Contents and Structure generated', 'bg=blue;fg=white', true),
            ''
        ));
    }

    /**
     * Create customer root content
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function createContentStructure(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $vendorName = false;
        while (!$vendorName) {
            $question = new Question($questionHelper->getQuestion('Customer Vendor name used to construct namespace', null));
            $question->setValidator(
                array(
                    'EdgarEz\SiteBuilderBundle\Command\Validators',
                    'validateVendorName'
                )
            );
            $vendorName = $questionHelper->ask($input, $output, $question);
        }

        $this->vendorName = $vendorName;

        $customerName = false;
        while (!$customerName) {
            $question = new Question($questionHelper->getQuestion('Customer name used to construct namespace', null));
            $question->setValidator(
                array(
                    'EdgarEz\SiteBuilderBundle\Command\Validators',
                    'validateVendorName'
                )
            );
            $customerName = $questionHelper->ask($input, $output, $question);
        }

        $this->customerName = $customerName;

        $basename = $this->vendorName . ProjectGenerator::MAIN;

        /** @var Content $content */
        $content = $this->getContainer()->get('edgar_ez_tools.content.service');
        $contentDefinition = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/datas/customercontent.yml'));
        $contentDefinition['parentLocationID'] = $this->getContainer()->getParameter(Container::underscore($basename) . '.default.customers_location_id');
        $contentDefinition['fields']['title']['value'] = $this->customerName;
        $contentAdded = $content->add($contentDefinition);

        $this->customerLocationID = $contentAdded->contentInfo->mainLocationId;
    }

    /**
     * Generate customer bundle
     *
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function createCustomerBundle(InputInterface $input, OutputInterface $output)
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
     * Update AppKernel.php adding new sitebuilder project bundle
     *
     * @param QuestionHelper $questionHelper question Helper
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     * @param KernelInterface $kernel symfony Kernel
     * @param string $namespace project namespace
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
     * Initialize customer generator tool
     *
     * @return CustomerGenerator customer generator tool
     */
    protected function createGenerator()
    {
        return new CustomerGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('kernel')
        );
    }
}
