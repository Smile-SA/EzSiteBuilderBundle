<?php

namespace EdgarEz\SiteBuilderBundle\Service\Task;


use EdgarEz\SiteBuilderBundle\Command\Validators;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Generator\SiteGenerator;
use EdgarEz\SiteBuilderBundle\Service\SiteService;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\LocationService;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class SiteTaskService extends BaseTaskService implements TaskInterface
{
    /** @var SiteService $siteService */
    protected $siteService;

    /** @var Filesystem $filesystem */
    protected $filesystem;

    /** @var Kernel $kernel */
    protected $kernel;

    /** @var LocationService $locationService */
    protected $locationService;

    /** @var string $kernelRootDir */
    protected $kernelRootDir;

    public function __construct(
        Filesystem $filesystem,
        Kernel $kernel,
        LocationService $locationService,
        SiteService $siteService,
        $kernelRootDir
    )
    {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->locationService = $locationService;
        $this->siteService = $siteService;
        $this->kernelRootDir = $kernelRootDir;

        $this->message = false;
    }

    public function validateParameters($parameters)
    {
        try {
            Validators::validateSiteName($parameters['siteName']);
            Validators::validateLocationID($parameters['model']);
            Validators::validateHost($parameters['host']);
            Validators::validateSiteaccessSuffix($parameters['suffix']);
        } catch (InvalidArgumentException $e) {
            throw new \Exception($e->getMessage());
        }

        try {
            $this->locationService->loadLocation($parameters['model']);
        } catch (\Exception $e) {
            throw new \Exception('Fail to load model');
        }
    }

    public function execute($command, array $parameters, Container $container)
    {
        switch ($command) {
            case 'generate':
                try {
                    /*
                    $this->validateParameters($parameters);

                    $modelLocation = $this->locationService->loadLocation($parameters['model']);;

                    $returnValue = $this->siteService->createSiteContent($contentLocationIDs['customerLocationID'], $contentLocationIDs['modelLocationID'], $parameters['siteName']);
                    $siteLocationID = $returnValue['siteLocationID'];
                    $excludeUriPrefixes = $returnValue['excludeUriPrefixes'];

                    $returnValue = $this->siteService->createMediaSiteContent($mediaLocationIDs['mediaCustomerLocationID'], $mediaLocationIDs['mediaModelLocationID'], $parameters['siteName']);
                    $mediaSiteLocationID = $returnValue['mediaSiteLocationID'];

                    $basename = substr(ProjectGenerator::BUNDLE, 0, -6);
                    $extensionAlias = 'edgarez_sb.' . Container::underscore($basename);
                    $vendorName = $container->getParameter($extensionAlias . '.default.vendor_name');

                    $generator = new SiteGenerator(
                        $this->filesystem,
                        $this->kernel
                    );
                    $generator->generate(
                        $siteLocationID,
                        $mediaSiteLocationID,
                        $vendorName,
                        $this->customerName,
                        $modelLocation->contentInfo->name,
                        $parameters['siteName'],
                        $excludeUriPrefixes,
                        $parameters['host'],
                        $parameters['mapuri'],
                        $parameters['siteaccessSuffix'],
                        $this->kernelRootDir . '/../src'
                    );
                    */
                } catch (\RuntimeException $e) {
                    $this->message = $e->getMessage();
                    return false;
                } catch (\Exception $e) {
                    $this->message = $e->getMessage();
                    return false;
                }
                break;
            case 'policy':
                try {
                    $this->validateParameters($parameters);
                } catch (\RuntimeException $e) {
                    $this->message = $e->getMessage();
                    return false;
                } catch (\Exception $e) {
                    $this->message = $e->getMessage();
                    return false;
                }
                break;
        }

        return true;
    }

    public function getMessage()
    {
        return $this->message;
    }

}
