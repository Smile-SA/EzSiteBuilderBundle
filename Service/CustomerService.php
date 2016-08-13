<?php

namespace EdgarEz\SiteBuilderBundle\Service;


use EdgarEz\ToolsBundle\Service\Content;
use EdgarEz\ToolsBundle\Service\Role;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use Symfony\Component\Yaml\Yaml;

class CustomerService
{
    private $roleService;
    private $locationService;
    private $userService;
    private $contentTypeService;

    private $content;
    private $role;

    public function __construct(
        RoleService $roleService,
        LocationService $locationService,
        UserService $userService,
        ContentTypeService $contentTypeService,
        Content $content,
        Role $role
    )
    {
        $this->roleService = $roleService;
        $this->locationService = $locationService;
        $this->userService = $userService;
        $this->contentTypeService = $contentTypeService;
        $this->content = $content;
        $this->role = $role;
    }

    public function createContentStructure($parentLocationID, $customerName)
    {
        $contentDefinition = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/datas/customercontent.yml'));
        $contentDefinition['parentLocationID'] = $parentLocationID;
        $contentDefinition['fields']['title']['value'] = $customerName;
        return $this->content->add($contentDefinition);
    }

    public function createMediaContentStructure($parentLocationID, $customerName)
    {
        $contentDefinition = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/datas/mediacustomercontent.yml'));
        $contentDefinition['parentLocationID'] = $parentLocationID;
        $contentDefinition['fields']['title']['value'] = $customerName;
        return $this->content->add($contentDefinition);
    }

    public function createUserGroups($parentCreatorLocationID, $parentEditorLocationID, $customerName)
    {
        $contents = array();

        $userGroupDefinition = Yaml::parse(file_get_contents(__DIR__. '/../Resources/datas/customerusergroup_creators.yml'));
        $userGroupDefinition['parentLocationID'] = $parentCreatorLocationID;
        $userGroupDefinition['fields']['name']['value'] = $customerName;
        $contents['customerUserCreatorsGroup'] = $this->content->add($userGroupDefinition);

        $userGroupDefinition = Yaml::parse(file_get_contents(__DIR__. '/../Resources/datas/customerusergroup_editors.yml'));
        $userGroupDefinition['parentLocationID'] = $parentEditorLocationID;
        $userGroupDefinition['fields']['name']['value'] = $customerName;
        $contents['customerUserEditorsGroup'] = $this->content->add($userGroupDefinition);

        return $contents;
    }

    public function createRoles(
        $customerName,
        $customerLocationID,
        $mediaCustomerLocationID,
        $customerUserCreatorsGroupLocationID,
        $customerUserEditorsGroupLocationID
    )
    {
        $returnValue = array();

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleCreator */
        $roleCreator = $this->role->add('SiteBuilder ' . $customerName . ' creator');
        $returnValue['customerRoleCreatorID'] = $roleCreator->id;
        $returnValue['roleCreator'] = $roleCreator;

        $this->role->addPolicy($roleCreator->id, 'content', 'read');
        $this->role->addPolicy($roleCreator->id, 'content', 'create');
        $this->role->addPolicy($roleCreator->id, 'content', 'edit');
        $this->role->addPolicy($roleCreator->id, 'user', 'login');

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleEditor */
        $roleEditor = $this->role->add('SiteBuilder ' . $customerName . ' editor');
        $returnValue['customerRoleEditorID'] = $roleEditor->id;
        $returnValue['roleEditor'] = $roleEditor;

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
                    '/' . implode('/', $mediaContentLocation->path) . '/'
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

        return $returnValue;
    }

    public function initializeUserCreator($userFirstName, $userLastName, $userEmail, $customerUserCreatorsGroupLocationID)
    {
        $userLogin = $userEmail;
        $userPassword = substr(str_shuffle(strtolower(sha1(rand() . time() . $userLogin))),0, 8);;

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier('edgar_ez_sb_user');
        $userCreateStruct = $this->userService->newUserCreateStruct($userLogin, $userEmail, $userPassword, 'eng-GB', $contentType);
        $userCreateStruct->setField('first_name', $userFirstName);
        $userCreateStruct->setField('last_name', $userLastName);

        $userGroupCreatorLocation = $this->locationService->loadLocation($customerUserCreatorsGroupLocationID);
        $userGroup = $this->userService->loadUserGroup($userGroupCreatorLocation->contentId);

        $this->userService->createUser($userCreateStruct, array($userGroup));

        return $userPassword;
    }
}
