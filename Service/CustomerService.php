<?php

namespace EdgarEz\SiteBuilderBundle\Service;

use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use EdgarEz\ToolsBundle\Service\Content;
use EdgarEz\ToolsBundle\Service\Role;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException;
use eZ\Publish\API\Repository\Exceptions\ContentValidationException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\LimitationValidationException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\Core\Repository\Values\User\PolicyDraft;
use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CustomerService
 * @package EdgarEz\SiteBuilderBundle\Service
 */
class CustomerService
{
    /** @var Kernel $kernel symfony kernel interface */
    private $kernel;

    /** @var RoleService $roleService eZ Role Service */
    private $roleService;

    /** @var LocationService $locationService eZ Location Service */
    private $locationService;

    /** @var UserService $userService eZ User Service */
    private $userService;

    /** @var ContentTypeService $contentTypeService eZ ContentType Service */
    private $contentTypeService;

    /** @var Content $content EdgarEz Content Service */
    private $content;

    /** @var Role $role EdgarEz Role Service */
    private $role;

    /** @var array $siteaccessGroups ezpublish siteaccess groups */
    private $siteaccessGroups;

    /**
     * CustomerService constructor.
     *
     * @param RoleService $roleService eZ role Service
     * @param LocationService $locationService eZ Location Service
     * @param UserService $userService eZ User Service
     * @param ContentTypeService $contentTypeService eZ ContentType Service
     * @param Content $content EdgarEz Content Service
     * @param Role $role EdgarEz Role Service
     * @param array $siteaccessGroups ezpublish siteaccess groups
     */
    public function __construct(
        Kernel $kernel,
        RoleService $roleService,
        LocationService $locationService,
        UserService $userService,
        ContentTypeService $contentTypeService,
        Content $content,
        Role $role,
        array $siteaccessGroups
    ) {
        $this->kernel = $kernel;
        $this->roleService = $roleService;
        $this->locationService = $locationService;
        $this->userService = $userService;
        $this->contentTypeService = $contentTypeService;
        $this->content = $content;
        $this->role = $role;
        $this->siteaccessGroups = $siteaccessGroups;
    }

    /**
     * Create user creator
     *
     * @param string $userFirstName first name
     * @param string $userLastName last name
     * @param string $userEmail email
     * @param int $customerUserCreatorsGroupLocationID group location ID
     * @return string
     */
    public function initializeUserCreator(
        $userFirstName,
        $userLastName,
        $userEmail,
        $customerUserCreatorsGroupLocationID
    ) {
        $userLogin = $userEmail;
        $userPassword = substr(str_shuffle(strtolower(sha1(rand() . time() . $userLogin))), 0, 8);
        ;

        try {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier('edgar_ez_sb_user');
            $userCreateStruct = $this->userService->newUserCreateStruct(
                $userLogin,
                $userEmail,
                $userPassword,
                'eng-GB',
                $contentType
            );
            $userCreateStruct->setField('first_name', $userFirstName);
            $userCreateStruct->setField('last_name', $userLastName);

            $userGroupCreatorLocation = $this->locationService->loadLocation($customerUserCreatorsGroupLocationID);
            $userGroup = $this->userService->loadUserGroup($userGroupCreatorLocation->contentId);

            $this->userService->createUser($userCreateStruct, array($userGroup));

            return $userPassword;
        } catch (NotFoundException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (UnauthorizedException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (ContentFieldValidationException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (ContentValidationException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (InvalidArgumentException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Create Customer content structure
     *
     * @param int $parentLocationID root content location ID
     * @param string $name customer name
     * @return array customer content root location ID
     */
    public function createContentStructure($parentLocationID, $name)
    {
        try {
            $contentDefinition = Yaml::parse(
                file_get_contents(
                    $this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/datas/customercontent.yml')
                )
            );
            $contentDefinition['parentLocationID'] = $parentLocationID;
            $contentDefinition['fields']['title']['value'] = $name;
            $contentAdded = $this->content->add($contentDefinition);

            return array(
                'customerLocationID' => $contentAdded->contentInfo->mainLocationId
            );
        } catch (ParseException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (\RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * Create Customer media structure
     *
     * @param int $parentLocationID root media location ID
     * @param string $name customer name
     * @return array customer media root location ID
     */
    public function createMediaContentStructure($parentLocationID, $name)
    {
        try {
            $contentDefinition = Yaml::parse(
                file_get_contents(
                    $this->kernel->locateResource('@EdgarEzSiteBuilderBundle/Resources/datas/mediacustomercontent.yml')
                )
            );
            $contentDefinition['parentLocationID'] = $parentLocationID;
            $contentDefinition['fields']['title']['value'] = $name;
            $contentAdded = $this->content->add($contentDefinition);

            return array(
                'mediaCustomerLocationID' => $contentAdded->contentInfo->mainLocationId
            );
        } catch (ParseException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (\RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * Create Customer user structure
     *
     * @param int $parentCreatorLocationID root user location ID for customer user group creator
     * @param int $parentEditorLocationID root user location ID for customer user group editor
     * @param string $name customer name
     * @return array customer users root location IDs
     */
    public function createUserGroups($parentCreatorLocationID, $parentEditorLocationID, $name)
    {
        $contents = array();

        try {
            $userGroupDefinition = Yaml::parse(
                file_get_contents(
                    $this->kernel->locateResource(
                        '@EdgarEzSiteBuilderBundle/Resources/datas/customerusergroup_creators.yml'
                    )
                )
            );
            $userGroupDefinition['parentLocationID'] = $parentCreatorLocationID;
            $userGroupDefinition['fields']['name']['value'] = $name;
            $contents['customerUserCreatorsGroup'] = $this->content->add($userGroupDefinition);

            $userGroupDefinition = Yaml::parse(
                file_get_contents(
                    $this->kernel->locateResource(
                        '@EdgarEzSiteBuilderBundle/Resources/datas/customerusergroup_editors.yml'
                    )
                )
            );
            $userGroupDefinition['parentLocationID'] = $parentEditorLocationID;
            $userGroupDefinition['fields']['name']['value'] = $name;
            $contents['customerUserEditorsGroup'] = $this->content->add($userGroupDefinition);

            return array(
                'customerUserCreatorsGroupLocationID' =>
                    $contents['customerUserCreatorsGroup']->contentInfo->mainLocationId,
                'customerUserEditorsGroupLocationID' =>
                    $contents['customerUserEditorsGroup']->contentInfo->mainLocationId
            );
        } catch (ParseException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (\RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * Initialize customer roles for creators and editors
     *
     * @param string $customerName customer name
     * @param int $customerLocationID customer root content location ID
     * @param int $mediaCustomerLocationID customer root media location ID
     * @param int $customerUserCreatorsGroupLocationID customer root user group creator location ID
     * @param int $customerUserEditorsGroupLocationID customer root user group editor location ID
     * @return array customer user roles IDs
     */
    public function createRoles(
        $customerName,
        $customerLocationID,
        $mediaCustomerLocationID,
        $customerUserCreatorsGroupLocationID,
        $customerUserEditorsGroupLocationID
    ) {
        try {
            /** @var \eZ\Publish\API\Repository\Values\User\Role $roleCreator */
            $roleCreator = $this->role->add('SiteBuilder ' . $customerName . ' creator');

            $this->role->addPolicy($roleCreator->id, 'content', 'read');
            $this->role->addPolicy($roleCreator->id, 'content', 'versionread');
            $this->role->addPolicy($roleCreator->id, 'content', 'create');
            $this->role->addPolicy($roleCreator->id, 'content', 'edit');
            $this->role->addPolicy($roleCreator->id, 'user', 'login');

            $this->role->addPolicy($roleCreator->id, 'sitebuilder', 'dashboard');
            $this->role->addPolicy($roleCreator->id, 'sitebuilder', 'sitegenerate');
            $this->role->addPolicy($roleCreator->id, 'sitebuilder', 'siteactivate');

            /** @var \eZ\Publish\API\Repository\Values\User\Role $roleEditor */
            $roleEditor = $this->role->add('SiteBuilder ' . $customerName . ' editor');

            $this->role->addPolicy($roleEditor->id, 'content', 'read');
            $this->role->addPolicy($roleEditor->id, 'content', 'create');
            $this->role->addPolicy($roleEditor->id, 'content', 'edit');
            $this->role->addPolicy($roleEditor->id, 'user', 'login');

            // Manage policy subtree limitation to the roles
            $contentLocation = $this->locationService->loadLocation($customerLocationID);
            $mediaContentLocation = $this->locationService->loadLocation($mediaCustomerLocationID);

            $userGroupCreatorLocation = $this->locationService->loadLocation($customerUserCreatorsGroupLocationID);
            $userGroupCreator = $this->userService->loadUserGroup($userGroupCreatorLocation->contentId);
            $userGroupEditorLocation = $this->locationService->loadLocation($customerUserEditorsGroupLocationID);
            $userGroupEditor = $this->userService->loadUserGroup($userGroupEditorLocation->contentId);
            $subtreeLimitation = new SubtreeLimitation(
                array(
                    'limitationValues' => array(
                        '/' . implode('/', $contentLocation->path) . '/',
                        '/' . implode('/', $mediaContentLocation->path) . '/',
                        '/' . implode('/', $userGroupCreatorLocation->path) . '/',
                        '/' . implode('/', $userGroupEditorLocation->path) . '/'
                    )
                )
            );

            $this->roleService->assignRoleToUserGroup(
                $roleCreator,
                $userGroupCreator,
                $subtreeLimitation
            );

            $this->roleService->assignRoleToUserGroup(
                $roleEditor,
                $userGroupEditor,
                $subtreeLimitation
            );

            $siteaccess = array();
            $siteaccessGroups = array_keys($this->siteaccessGroups);
            foreach ($siteaccessGroups as $sg) {
                if (strpos($sg, 'edgarezsb_models_') === 0) {
                    $sg = substr($sg, strlen('edgarezsb_models_'));
                    $siteaccess[] = sprintf('%u', crc32($sg));
                }
            }

            $this->role->addSiteaccessLimitation($roleCreator, $siteaccess);
            $this->role->addSiteaccessLimitation($roleEditor, $siteaccess);

            return array(
                'customerRoleCreatorID' => $roleCreator->id,
                'customerRoleEditorID'  => $roleEditor->id
            );
        } catch (UnauthorizedException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (NotFoundException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (LimitationValidationException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (InvalidArgumentException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (\RuntimeException $e) {
            throw $e;
        }
    }

    public function updateGlobalRole($customerUserCreatorsGroupLocationID, $customerUserEditorsGroupLocationID)
    {
        try {
            /** @var \eZ\Publish\API\Repository\Values\User\Role $role */
            $role = $this->roleService->loadRoleByIdentifier('SiteBuilder');

            $roleDraft = $this->roleService->createRoleDraft($role);

            /** @var Policy[] $policies */
            $policies = $roleDraft->policies;
            foreach ($policies as $policy) {
                if ($policy->module == 'content' && $policy->function == 'read') {
                    /** @var Limitation[] $limitations */
                    $limitations = $policy->getLimitations();
                    foreach ($limitations as $limitation) {
                        if ($limitation->getIdentifier() == 'Node') {
                            $limitationValues = $limitation->limitationValues;
                            $limitationValues[] = $customerUserCreatorsGroupLocationID;
                            $limitationValues[] = $customerUserEditorsGroupLocationID;
                            $limitation->limitationValues = $limitationValues;

                            $policyUpdateStruct = new PolicyUpdateStruct();
                            $policyUpdateStruct->addLimitation($limitation);

                            $policyDraft = new PolicyDraft(
                                [
                                    'innerPolicy' => new \eZ\Publish\Core\Repository\Values\User\Policy(
                                        [
                                            'id' => $policy->id,
                                            'module' => 'content',
                                            'function' => 'read',
                                            'roleId' => $roleDraft->id
                                        ]
                                    )
                                ]
                            );

                            $this->roleService->updatePolicyByRoleDraft(
                                $roleDraft,
                                $policyDraft,
                                $policyUpdateStruct
                            );
                            $this->roleService->publishRoleDraft($roleDraft);
                        }
                    }
                }
            }
        } catch (UnauthorizedException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (NotFoundException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (LimitationValidationException $e) {
            throw new \RuntimeException($e->getMessage());
        } catch (InvalidArgumentException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Check if customer bundle exists
     *
     * @param string $customerName
     * @param string $vendorName
     * @param string $dir
     * @return bool true|false if customer bundle exists
     */
    public function exists($customerName, $vendorName, $dir)
    {
        return file_exists($dir . '/' . $vendorName . '/' . ProjectGenerator::CUSTOMERS . '/' . $customerName);
    }
}
