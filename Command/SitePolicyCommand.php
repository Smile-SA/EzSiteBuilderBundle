<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Generator\SiteGenerator;
use EdgarEz\SiteBuilderBundle\Service\SiteService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class SitePolicyCommand
 *
 * @package EdgarEz\SiteBuilderBundle\Command
 */
class SitePolicyCommand extends BaseContainerAwareCommand
{
    /**
     * @var string $customerName customer name
     */
    protected $customerName;

    /** @var string $siteName site name */
    protected $siteName;

    /**
     * Configure Customer generator command
     */
    protected function configure()
    {
        $this
            ->setName('edgarez:sitebuilder:site:policy')
            ->setDescription('Manage SiteBuilder Site limitation policies');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input input console
     * @param OutputInterface $output output console
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adminID = $this->getContainer()->getParameter('edgar_ez_tools.adminid');
        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->setCurrentUser($repository->getUserService()->loadUser($adminID));

        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'SiteBuilder Site limitation policies managment for user creator/editor');

        $this->getVendorNameDir();

        $this->askCustomerName($input, $output);
        $this->askSiteName($input, $output);

        $this->addSiteaccessLimitation($this->customerName, Container::underscore($this->vendorName . $this->customerName . $this->siteName));

        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock(
                array(
                    'Siteaccess Limitation are set for your customer creator/editor roles'
                ),
                'bg=blue;fg=white',
                true
            ),
            ''
        ));
    }

    protected function askCustomerName(InputInterface $input, OutputInterface $output)
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

    protected function askSiteName(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $siteName = false;
        $question = new Question($questionHelper->getQuestion('Site name used to construct namespace', null));
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
    }

    protected function addSiteaccessLimitation($customerName, $siteaccessName)
    {
        /** @var SiteService $siteSerice */
        $siteService = $this->getContainer()->get('edgar_ez_site_builder.site.service');

        $extensionAlias = Container::underscore(ProjectGenerator::CUSTOMERS . $customerName . CustomerGenerator::SITES);
        $roleCreatorID = $this->getContainer()->getParameter('edgarez_sb.customer.' . $extensionAlias . '.default.customer_user_creator_role_id');
        $roleEditorID = $this->getContainer()->getParameter('edgarez_sb.customer.' . $extensionAlias . '.default.customer_user_editor_role_id');

        /** @var Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $roleService = $repository->getRoleService();

        $roleCreator = $roleService->loadRole($roleCreatorID);
        $roleEditor = $roleService->loadRole($roleEditorID);

        $siteService->addSiteaccessLimitation($roleCreator, $roleEditor, $siteaccessName);
    }

    /**
     * Initialize customer generator tool
     *
     * @return SiteGenerator customer generator tool
     */
    protected function createGenerator()
    {
        return null;
    }
}
