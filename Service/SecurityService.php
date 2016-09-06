<?php

namespace EdgarEz\SiteBuilderBundle\Service;

use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class SecurityService
{
    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /** @var AuthorizationChecker $authorizationChecker */
    protected $authorizationChecker;

    /** @var RoleService $roleService */
    protected $roleService;

    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        RoleService $roleService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->roleService = $roleService;
    }

    public function checkAuthorization($func)
    {
        if (!$this->authorizationChecker->isGranted(new Attribute('sitebuilder', $func))) {
            return false;
        }

        /**
         * Users with policies module *, function * will not have access
         * to functions sitegenerate from sitebuilder module.
         */
        if ($func == 'sitegenerate' ||
            $func == 'siteactivate' ||
            $func == 'usergenerate'
        ) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            /** @var Policy[] $policies */
            $policies = $this->roleService->loadPoliciesByUserId($user->getAPIUser()->id);
            foreach ($policies as $policy) {
                if ($policy->module == '*' && $policy->function == '*') {
                    return false;
                }
            }
        }

        return true;
    }
}
