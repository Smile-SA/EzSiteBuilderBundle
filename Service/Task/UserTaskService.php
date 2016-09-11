<?php

namespace EdgarEz\SiteBuilderBundle\Service\Task;

use EdgarEz\SiteBuilderBundle\Command\Validators;
use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\SiteBuilderBundle\Mail\Sender;
use EdgarEz\SiteBuilderBundle\Service\CustomerService;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\UserService;
use Symfony\Component\DependencyInjection\Container;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;

class UserTaskService extends BaseTaskService implements TaskInterface
{
    /** @var CustomerService $customerService */
    protected $customerService;

    /** @var UserService $userService */
    protected $userService;

    /** @var LocationService $locationService */
    protected $locationService;

    /** @var LanguageService $languageService */
    protected $languageService;

    /** @var Sender $mailer */
    protected $mailer;

    /** @var string $sysadminEmail */
    protected $sysadminEmail;

    public function __construct(
        CustomerService $customerService,
        UserService $userService,
        LocationService $locationService,
        LanguageService $languageService,
        Sender $mailer,
        $sysadminEmail
    ) {
        $this->customerService = $customerService;
        $this->userService = $userService;
        $this->locationService = $locationService;
        $this->languageService = $languageService;
        $this->mailer = $mailer;
        $this->sysadminEmail = $sysadminEmail;

        $this->message = false;
    }

    public function validateParameters($parameters)
    {
        try {
            Validators::validateFirstName($parameters['userFirstName']);
            Validators::validateLastName($parameters['userLastName']);
            Validators::validateEmail($parameters['userEmail']);

            if ($this->customerService->emailExists($parameters['userEmail'])) {
                throw new \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException(
                    'email', 'User with same email already exists'
                );
            }
        } catch (InvalidArgumentException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function execute($command, array $parameters, Container $container, $userID)
    {
        switch ($command) {
            case 'generate':
                try {
                    $this->validateParameters($parameters);

                    $userType = ($parameters['userType'] == 0) ? 'editors' : 'creators';
                    $customerName = strtolower($this->getCustomerName($userID));
                    $settings = 'edgarez_sb.customer.' . ProjectGenerator::CUSTOMERS .
                        '_' . $customerName . '_' . CustomerGenerator::SITES .
                        '.default.customer_user_' . $userType . '_group_location_id';

                    $languageCode = $this->languageService->getDefaultLanguageCode();

                    // Generate first user creator
                    $userPassword = $this->customerService->initializeUser(
                        $languageCode,
                        $parameters['userFirstName'],
                        $parameters['userLastName'],
                        $parameters['userEmail'],
                        $container->getParameter($settings)
                    );

                    $this->mailer->send(
                        'new user: ' . $parameters['userEmail'] . '/' . $userPassword,
                        'new user',
                        $this->sysadminEmail,
                        $parameters['userEmail']
                    );
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

    protected function getCustomerName($userID)
    {
        $user = $this->userService->loadUser($userID);
        $userLocation = $this->locationService->loadLocation($user->contentInfo->mainLocationId);

        $parent = $this->locationService->loadLocation($userLocation->parentLocationId);
        return $parent->contentInfo->name;
    }
}
