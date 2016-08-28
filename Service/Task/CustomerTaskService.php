<?php

namespace EdgarEz\SiteBuilderBundle\Service\Task;

use EdgarEz\SiteBuilderBundle\Command\Validators;
use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Service\CustomerService;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class CustomerTaskService extends BaseTaskService implements TaskInterface
{
    /** @var CustomerService $customerService */
    protected $customerService;

    /** @var Filesystem $filesystem */
    protected $filesystem;

    /** @var Kernel $kernel */
    protected $kernel;

    /** @var string $kernelRootDir */
    protected $kernelRootDir;

    public function __construct(
        Filesystem $filesystem,
        Kernel $kernel,
        CustomerService $customerService,
        $kernelRootDir
    )
    {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->customerService = $customerService;
        $this->kernelRootDir = $kernelRootDir;

        $this->message = false;
    }

    public function validateParameters($parameters)
    {
        try {
            Validators::validateCustomerName($parameters['customerName']);
            Validators::validateFirstName($parameters['userFirstName']);
            Validators::validateLastName($parameters['userLastName']);
            Validators::validateEmail($parameters['userEmail']);
        } catch (InvalidArgumentException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function execute($command, array $parameters, Container $container)
    {
        switch ($command) {
            case 'generate':
                try {
                    $this->validateParameters($parameters);

                    $basename = ProjectGenerator::MAIN;

                    $parentLocationID = $container->getParameter('edgarez_sb.' . Container::underscore($basename) . '.default.customers_location_id');
                    $returnValue = $this->customerService->createContentStructure($parentLocationID, $parameters['customerName']);
                    $customerLocationID = $returnValue['customerLocationID'];

                    $parentLocationID = $container->getParameter('edgarez_sb.' . Container::underscore($basename) . '.default.media_customers_location_id');
                    $returnValue = $this->customerService->createMediaContentStructure($parentLocationID, $parameters['customerName']);
                    $mediaCustomerLocationID = $returnValue['mediaCustomerLocationID'];

                    $parentCreatorLocationID = $container->getParameter('edgarez_sb.' . Container::underscore($basename) . '.default.user_creators_location_id');
                    $parentEditorLocationID = $container->getParameter('edgarez_sb.' . Container::underscore($basename) . '.default.user_editors_location_id');
                    $returnValue = $this->customerService->createUserGroups($parentCreatorLocationID, $parentEditorLocationID, $parameters['customerName']);
                    $customerUserCreatorsGroupLocationID = $returnValue['customerUserCreatorsGroupLocationID'];
                    $customerUserEditorsGroupLocationID = $returnValue['customerUserEditorsGroupLocationID'];

                    $returnValue = $this->customerService->createRoles(
                        $parameters['customerName'],
                        $customerLocationID,
                        $mediaCustomerLocationID,
                        $customerUserCreatorsGroupLocationID,
                        $customerUserEditorsGroupLocationID
                    );
                    $customerRoleCreatorID = $returnValue['customerRoleCreatorID'];
                    $customerRoleEditorID = $returnValue['customerRoleEditorID'];

                    $basename = substr(ProjectGenerator::BUNDLE, 0, -6);
                    $extensionAlias = 'edgarez_sb.' . Container::underscore($basename);
                    $vendorName = $container->getParameter($extensionAlias . '.default.vendor_name');

                    $generator = new CustomerGenerator(
                        $this->filesystem,
                        $this->kernel
                    );
                    $generator->generate(
                        $customerLocationID,
                        $mediaCustomerLocationID,
                        $customerUserCreatorsGroupLocationID,
                        $customerUserEditorsGroupLocationID,
                        $customerRoleCreatorID,
                        $customerRoleEditorID,
                        $vendorName,
                        $parameters['customerName'],
                        $this->kernelRootDir . '/../src'
                    );

                    $namespace = $vendorName . '\\' . ProjectGenerator::CUSTOMERS . '\\' . $parameters['customerName'] . '\\' . CustomerGenerator::BUNDLE ;
                    $bundle = $vendorName . ProjectGenerator::CUSTOMERS . $parameters['customerName'] . CustomerGenerator::BUNDLE;
                    $this->updateKernel($this->kernel, $namespace, $bundle);
                } catch (\RuntimeException $e) {
                    $this->message = $e->getMessage();
                    return false;
                } catch (\Exception $e) {
                    $this->message = $e->getMessage();
                    return false;
                }
                break;
            default:
                break;
        }

        return true;
    }
}
