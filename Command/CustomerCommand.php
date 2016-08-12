<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\ToolsBundle\Service\Content;
use EdgarEz\ToolsBundle\Service\Role;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
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

        $this->init($input, $output);

        $this->createContentStructure($input, $output);
        $this->createMediaContentStructure($input, $output);
        $this->createUserGroups($input, $output);
        $this->createRoles($input, $output);
        $this->initializeUserCreator($input, $output);

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
    protected function createContentStructure(InputInterface $input, OutputInterface $output)
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
     * Create media customer root content
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function createMediaContentStructure(InputInterface $input, OutputInterface $output)
    {
        $basename = $this->vendorName . ProjectGenerator::MAIN;

        /** @var Content $content */
        $content = $this->getContainer()->get('edgar_ez_tools.content.service');
        $contentDefinition = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/datas/mediacustomercontent.yml'));
        $contentDefinition['parentLocationID'] = $this->getContainer()->getParameter(Container::underscore($basename) . '.default.media_customers_location_id');
        $contentDefinition['fields']['title']['value'] = $this->customerName;
        $contentAdded = $content->add($contentDefinition);

        $this->mediaCustomerLocationID = $contentAdded->contentInfo->mainLocationId;
    }

    /**
     * Create customer user groups (creator and editor)
     *
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function createUserGroups(InputInterface $input, OutputInterface $output)
    {
        $basename = $this->vendorName . ProjectGenerator::MAIN;

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
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function createRoles(InputInterface $input, OutputInterface $output)
    {
        /** @var Role $roleService */
        $roleService = $this->getContainer()->get('edgar_ez_tools.role.service');

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleCreator */
        $roleCreator = $roleService->add('SiteBuilder ' . $this->customerName . ' creator');
        $this->customerRoleCreatorID = $roleCreator->id;
        $output->writeln('Add user creator role');
        $roleService->addPolicy($roleCreator->id, 'content', 'read');
        $roleService->addPolicy($roleCreator->id, 'content', 'create');
        $roleService->addPolicy($roleCreator->id, 'content', 'edit');

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleEditor */
        $roleEditor = $roleService->add('SiteBuilder ' . $this->customerName . ' editor');
        $this->customerRoleEditorID = $roleEditor->id;
        $output->writeln('Add user editor role');
        $roleService->addPolicy($roleEditor->id, 'content', 'read');
        $roleService->addPolicy($roleEditor->id, 'content', 'create');
        $roleService->addPolicy($roleEditor->id, 'content', 'edit');

        // Manager policy limitation to the rôles
        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $locationService = $repository->getLocationService();
        $userService = $repository->getUserService();

        $contentLocation = $locationService->loadLocation($this->customerLocationID);
        $mediaContentLocation = $locationService->loadLocation($this->mediaCustomerLocationID);

        $userGroupCreatorLocation = $locationService->loadLocation($this->customerUserCreatorsGroupLocationID);
        $userGroupCreator = $userService->loadUserGroup($userGroupCreatorLocation->contentId);
        $userGroupEditorLocation = $locationService->loadLocation($this->customerUserEditorsGroupLocationID);
        $userGroupEditor = $userService->loadUserGroup($userGroupEditorLocation->contentId);
        $subtreeLimitation = new SubtreeLimitation(
            array(
                'limitationValues' => array(
                    '/' . implode('/', $contentLocation->path) . '/',
                    '/' . implode('/', $mediaContentLocation->path) . '/'
                )
            )
        );

        $repository->setCurrentUser($repository->getUserService()->loadUser(14));

        /** @var RoleService $roleService */
        $roleService = $repository->getRoleService();
        $roleService->assignRoleToUserGroup(
            $roleCreator,
            $userGroupCreator,
            $subtreeLimitation
        );

        $roleService->assignRoleToUserGroup(
            $roleEditor,
            $userGroupEditor,
            $subtreeLimitation
        );
    }

    /**
     * Initialize customer user creator
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function initializeUserCreator(InputInterface $input, OutputInterface $output)
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

        $userLogin = $userEmail;
        $userPassword = substr(str_shuffle(strtolower(sha1(rand() . time() . $userLogin))),0, 8);;

        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        $userService = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('edgar_ez_sb_user');
        $userCreateStruct = $userService->newUserCreateStruct($userLogin, $userEmail, $userPassword, 'eng-GB', $contentType);
        $userCreateStruct->setField('first_name', $userFirstName);
        $userCreateStruct->setField('last_name', $userLastName);

        $userGroupCreatorLocation = $locationService->loadLocation($this->customerUserCreatorsGroupLocationID);
        $userGroup = $userService->loadUserGroup($userGroupCreatorLocation->contentId);

        $userService->createUser($userCreateStruct, array($userGroup));

        $questionHelper->writeSection($output, array(
                '',
                'New user created: ',
                'user login : ' . $userLogin,
                'user password : ' . $userPassword,
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
