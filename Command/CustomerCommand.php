<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\ToolsBundle\Service\Content;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Yaml\Yaml;

class CustomerCommand extends BaseContainerAwareCommand
{
    /**
     * @var int $customerLocationID customer content location ID
     */
    protected $customerLocationID;

    /**
     * @var int $customerUserCreatorsGroupLocationID customer user creators group location ID
     */
    protected $customerUserCreatorsGroupLocationID;

    /**
     * @var int $customerUserEditorsGroupLocationID customer user editors group location ID
     */
    protected $customerUserEditorsGroupLocationID;

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
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'SiteBuilder Customer initialization');

        $this->createContentStructure($input, $output);
        $this->createCustomerBundle($input, $output);
        $this->createUserGroups($input, $output);

        /** @var CustomerGenerator $generator */
        $generator = $this->getGenerator();
        $generator->generate(
            $this->customerLocationID,
            $this->customerUserCreatorsGroupLocationID,
            $this->customerUserEditorsGroupLocationID,
            $this->vendorName,
            $this->customerName,
            $this->dir
        );

        $namespace = $this->vendorName . '\\' . ProjectGenerator::CUSTOMERS . '\\' . $this->customerName . '\\' . CustomerGenerator::BUNDLE ;
        $bundle = $this->vendorName . ProjectGenerator::CUSTOMERS . $this->customerName . CustomerGenerator::BUNDLE;
        $this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle);

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
        $question = new Question($questionHelper->getQuestion('Customer Vendor name used to construct namespace', null));
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

        $customerName = false;
        $question = new Question($questionHelper->getQuestion('Customer name used to construct namespace', null));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateVendorName'
            )
        );

        while (!$customerName) {
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
     * Create customer user groups (creator and editor)
     *
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function createUserGroups(InputInterface $input, OutputInterface $output)
    {
        $basename = $this->vendorName . ProjectGenerator::MAIN ;

        $content = $this->getContainer()->get('edgar_ez_tools.content.service');

        $userGroupDefinition = Yaml::parse(file_get_contents(__DIR__. '/../Resources/datas/customerusergroup_creators.yml'));
        $userGroupDefinition['parentLocationID'] = $this->getContainer()->getParameter(Container::underscore($basename) . '.default.user_creators_location_id');
        $userGroupDefinition['fields']['name']['value'] = $this->customerName;
        /** @var \eZ\Publish\Core\REST\Client\Values\Content\Content $contentAdded */
        $contentAdded = $content->add($userGroupDefinition);
        $output->writeln('User group <info>' . $contentAdded->contentInfo->name . ' creators</info> created');

        $this->customerUserCreatorsGroupLocationID = $contentAdded->contentInfo->mainLocationId;

        $userGroupDefinition = Yaml::parse(file_get_contents(__DIR__. '/../Resources/datas/customerusergroup_editors.yml'));
        $userGroupDefinition['parentLocationID'] = $this->getContainer()->getParameter(Container::underscore($basename) . '.default.user_editors_location_id');
        $userGroupDefinition['fields']['name']['value'] = $this->customerName;
        /** @var \eZ\Publish\Core\REST\Client\Values\Content\Content $contentAdded */
        $contentAdded = $content->add($userGroupDefinition);
        $output->writeln('User group <info>' . $contentAdded->contentInfo->name . ' editors</info> created');

        $this->customerUserEditorsGroupLocationID = $contentAdded->contentInfo->mainLocationId;
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
