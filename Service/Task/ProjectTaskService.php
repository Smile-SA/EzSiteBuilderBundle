<?php

namespace EdgarEz\SiteBuilderBundle\Service\Task;

use EdgarEz\SiteBuilderBundle\Command\Validators;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Service\InstallService;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\LocationService;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class ProjectTaskService
 *
 * @package EdgarEz\SiteBuilderBundle\Service\Task
 */
class ProjectTaskService extends BaseTaskService implements TaskInterface
{
    /**
     * @var int $modelsLocationID root location ID for models content
     */
    protected $modelsLocationID;

    /**
     * @var int $customersLocationID root location ID for customers site content
     */
    protected $customersLocationID;

    /**
     * @var int $mediaModelsLocationID media root location ID for models content
     */
    protected $mediaModelsLocationID;

    /**
     * @var int $mediaCustomersLocationID media root location ID for customers site content
     */
    protected $mediaCustomersLocationID;

    /** @var int $userGroupParenttLocationID user group root location ID */
    protected $userGroupParenttLocationID;

    /**
     * @var int $userCreatorsLocationID root locationID for creator users
     */
    protected $userCreatorsLocationID;

    /**
     * @var int $userEditorsLocationID root locationID for editors users
     */
    protected $userEditorsLocationID;

    /** @var Filesystem $filesystem */
    protected $filesystem;

    /** @var Kernel $kernel */
    protected $kernel;

    /** @var LocationService $locationService */
    protected $locationService;

    /** @var string $kernelRootDir */
    protected $kernelRootDir;

    /** @var InstallService $installService */
    protected $installService;

    /**
     * ProjectTaskService constructor.
     *
     * @param Filesystem      $filesystem
     * @param Kernel          $kernel
     * @param LocationService $locationService
     * @param InstallService  $installService
     * @param                 $kernelRootDir
     */
    public function __construct(
        Filesystem $filesystem,
        Kernel $kernel,
        LocationService $locationService,
        InstallService $installService,
        $kernelRootDir
    ) {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->locationService = $locationService;
        $this->installService = $installService;
        $this->kernelRootDir = $kernelRootDir;

        $this->message = false;
    }

    /**
     * Validate task parameters
     *
     * @param array $parameters
     * @throws \Exception
     */
    public function validateParameters($parameters)
    {
        try {
            Validators::validateVendorName($parameters['vendorName']);
            Validators::validateLocationID($parameters['contentLocationID']);
            Validators::validateLocationID($parameters['mediaLocationID']);
            Validators::validateLocationID($parameters['userLocationID']);
        } catch (InvalidArgumentException $e) {
            throw new \Exception($e->getMessage());
        }

        try {
            $this->locationService->loadLocation($parameters['contentLocationID']);
        } catch (\Exception $e) {
            throw new \Exception('Fail to load root content location');
        }

        try {
            $this->locationService->loadLocation($parameters['mediaLocationID']);
        } catch (\Exception $e) {
            throw new \Exception('Fail to load root media location');
        }

        try {
            $this->locationService->loadLocation($parameters['userLocationID']);
        } catch (\Exception $e) {
            throw new \Exception('Fail to load root user location');
        }
    }

    /**
     * Execute task
     *
     * @param string $command
     * @param array $parameters
     * @return bool
     */
    public function execute($command, array $parameters, Container $container, $userID)
    {
        switch ($command) {
            case 'install':
                try {
                    $this->validateParameters($parameters);

                    $this->installService->createContentTypeGroup();

                    $returnValue = $this->installService->createContentStructure($parameters['contentLocationID']);
                    $this->modelsLocationID = $returnValue['modelsLocationID'];
                    $this->customersLocationID = $returnValue['customersLocationID'];

                    $returnValue = $this->installService->createMediaContentStructure($parameters['mediaLocationID']);
                    $this->mediaModelsLocationID = $returnValue['mediaModelsLocationID'];
                    $this->mediaCustomersLocationID = $returnValue['mediaCustomersLocationID'];

                    $returnValue = $this->installService->createUserStructure($parameters['userLocationID']);
                    $this->userGroupParenttLocationID = $returnValue['userGroupParenttLocationID'];
                    $this->userCreatorsLocationID = $returnValue['userCreatorsLocationID'];
                    $this->userEditorsLocationID = $returnValue['userEditorsLocationID'];

                    $contentLocation = $this->locationService->loadLocation($this->customersLocationID);
                    $mediaLocation = $this->locationService->loadLocation($this->mediaCustomersLocationID);
                    $userCreatorsLocation = $this->locationService->loadLocation($this->userCreatorsLocationID);
                    $userEditorsLocation = $this->locationService->loadLocation($this->userEditorsLocationID);

                    $locationIDs = array_merge(
                        $contentLocation->path,
                        $mediaLocation->path,
                        $userCreatorsLocation->path,
                        $userEditorsLocation->path
                    );
                    $locationIDs = array_unique($locationIDs, SORT_NUMERIC);
                    $this->installService->createRole($this->userGroupParenttLocationID, $locationIDs);

                    $generator = new ProjectGenerator(
                        $this->filesystem,
                        $this->kernel
                    );
                    $generator->generate(
                        $this->modelsLocationID,
                        $this->customersLocationID,
                        $this->mediaModelsLocationID,
                        $this->mediaCustomersLocationID,
                        $this->userCreatorsLocationID,
                        $this->userEditorsLocationID,
                        $parameters['vendorName'],
                        $this->kernelRootDir . '/../src'
                    );

                    $namespace = $parameters['vendorName'] . '\\' . ProjectGenerator::BUNDLE;
                    $bundle = $parameters['vendorName'] . ProjectGenerator::BUNDLE;
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
