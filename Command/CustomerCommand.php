<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Service\CustomerService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\User\Role;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;

class CustomerCommand extends BaseContainerAwareCommand
{
    /**
     * @var int $customerLocationID customer content location ID
     */
    protected $customerLocationID;

    /**
     * @var int $mediaCustomerLocationID media customer content location ID
     */
    protected $mediaCustomerLocationID;

    /**
     * @var int $customerUserCreatorsGroupLocationID customer user creators group location ID
     */
    protected $customerUserCreatorsGroupLocationID;

    /**
     * @var int $customerUserEditorsGroupLocationID customer user editors group location ID
     */
    protected $customerUserEditorsGroupLocationID;

    /** @var int $customerRoleCreatorID creator role ID */
    protected $customerRoleCreatorID;

    /** @var int $customerRoleEditorID editor role ID */
    protected $customerRoleEditorID;

    /**
     * @var string $customerName customer name
     */
    protected $customerName;

    protected $userFirstName;
    protected $userLastName;
    protected $userEmail;

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
        $adminID = $this->getContainer()->getParameter('edgar_ez_tools.adminid');
        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->setCurrentUser($repository->getUserService()->loadUser($adminID));

        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'SiteBuilder Customer initialization');

        $this->getVendorNameDir();

        $this->askContentStructure($input, $output);
        $this->askUserCreator($input, $output);

        $this->createContentStructure($output);
        $this->createMediaContentStructure($output);
        $this->createUserGroups($output);
        $this->createRoles($output);
        $this->initializeUserCreator($output);

        /** @var CustomerGenerator $generator */
        $generator = $this->getGenerator();
        $generator->generate(
            $this->customerLocationID,
            $this->mediaCustomerLocationID,
            $this->customerUserCreatorsGroupLocationID,
            $this->customerUserEditorsGroupLocationID,
            $this->customerRoleCreatorID,
            $this->customerRoleEditorID,
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
    protected function askContentStructure(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

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
    }

    protected function createContentStructure(OutputInterface $output)
    {
        $basename = ProjectGenerator::MAIN;

        /** @var CustomerService $customerService */
        $customerService = $this->getContainer()->get('edgar_ez_site_builder.customer.service');

        $parentLocationID = $this->getContainer()->getParameter('edgarez_sb.' . Container::underscore($basename) . '.default.customers_location_id');
        $contentAdded = $customerService->createContentStructure($parentLocationID, $this->customerName);
        $output->writeln('Content Structure created');

        $this->customerLocationID = $contentAdded->contentInfo->mainLocationId;
    }

    /**
     * Create media customer root content
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function createMediaContentStructure(OutputInterface $output)
    {
        $basename = ProjectGenerator::MAIN;

        /** @var CustomerService $customerService */
        $customerService = $this->getContainer()->get('edgar_ez_site_builder.customer.service');

        $parentLocationID = $this->getContainer()->getParameter('edgarez_sb.' . Container::underscore($basename) . '.default.media_customers_location_id');
        $contentAdded = $customerService->createMediaContentStructure($parentLocationID, $this->customerName);
        $output->writeln('Media Content Structure created');

        $this->mediaCustomerLocationID = $contentAdded->contentInfo->mainLocationId;
    }

    /**
     * Create customer user groups (creator and editor)
     *
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function createUserGroups(OutputInterface $output)
    {
        $basename = ProjectGenerator::MAIN;

        /** @var CustomerService $customerService */
        $customerService = $this->getContainer()->get('edgar_ez_site_builder.customer.service');

        $parentCreatorLocationID = $this->getContainer()->getParameter('edgarez_sb.' . Container::underscore($basename) . '.default.user_creators_location_id');
        $parentEditorLocationID = $this->getContainer()->getParameter('edgarez_sb.' . Container::underscore($basename) . '.default.user_editors_location_id');
        $contents = $customerService->createUserGroups($parentCreatorLocationID, $parentEditorLocationID, $this->customerName);
        $output->writeln('User groups created');

        $this->customerUserCreatorsGroupLocationID = $contents['customerUserCreatorsGroup']->contentInfo->mainLocationId;
        $this->customerUserEditorsGroupLocationID = $contents['customerUserEditorsGroup']->contentInfo->mainLocationId;
    }

    /**
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function createRoles(OutputInterface $output)
    {
        /** @var CustomerService $customerService */
        $customerService = $this->getContainer()->get('edgar_ez_site_builder.customer.service');

        /** @var Role[] $roles */
        $roles = $customerService->createRoles(
            $this->customerName,
            $this->customerLocationID,
            $this->mediaCustomerLocationID,
            $this->customerUserCreatorsGroupLocationID,
            $this->customerUserEditorsGroupLocationID
        );

        $this->customerRoleCreatorID = $roles['roleCreator']->id;
        $this->customerRoleEditorID = $roles['roleEditor']->id;
        $output->writeln('Roles created');
    }

    /**
     * Initialize customer user creator
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function askUserCreator(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Initialize customeruser creator account');

        $userFirstName = false;
        $question = new Question($questionHelper->getQuestion('First name', null));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateFirstName'
            )
        );

        while (!$userFirstName) {
            $userFirstName = $questionHelper->ask($input, $output, $question);
        }

        $this->userFirstName = $userFirstName;

        $userLastName = false;
        $question = new Question($questionHelper->getQuestion('Last name', null));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateLastName'
            )
        );

        while (!$userLastName) {
            $userLastName = $questionHelper->ask($input, $output, $question);
        }

        $this->userLastName = $userLastName;

        $userEmail = false;
        $question = new Question($questionHelper->getQuestion('User email', null));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateEmail'
            )
        );

        while (!$userEmail) {
            $userEmail = $questionHelper->ask($input, $output, $question);
        }

        $this->userEmail = $userEmail;
    }

    protected function initializeUserCreator(OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        /** @var CustomerService $customerService */
        $customerService = $this->getContainer()->get('edgar_ez_site_builder.customer.service');
        $output->writeln('User creator initialized');

        $userPassword = $customerService->initializeUserCreator(
            $this->userFirstName,
            $this->userLastName,
            $this->userEmail,
            $this->customerUserCreatorsGroupLocationID
        );

        $questionHelper->writeSection(
            $output,
            array(
                '',
                'user login: ' . $this->userEmail,
                'user password: ' . $userPassword ,
                ''
            )
        );
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
