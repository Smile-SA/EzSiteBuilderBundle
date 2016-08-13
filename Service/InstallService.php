<?php

namespace EdgarEz\SiteBuilderBundle\Service;

use EdgarEz\ToolsBundle\Service\Content;
use EdgarEz\ToolsBundle\Service\ContentType;
use EdgarEz\ToolsBundle\Service\ContentTypeGroup;
use eZ\Publish\API\Repository\ContentTypeService;
use Symfony\Component\Yaml\Yaml;

class InstallService
{
    private $contentTypeService;

    private $contentTypeGroup;
    private $contentType;
    private $content;

    public function __construct(
        ContentTypeService $contentTypeService,
        ContentTypeGroup $contentTypeGroup,
        ContentType $contentType,
        Content $content
    )
    {
        $this->contentTypeService = $contentTypeService;

        $this->contentTypeGroup = $contentTypeGroup;
        $this->contentType = $contentType;
        $this->content = $content;
    }

    public function createContentTypeGroup()
    {
        return $this->contentTypeGroup->add('SiteBuilder');
    }

    public function createContentTypes(\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup)
    {
        $contentTypes = array();

        $contentTypeDefinitions = glob(__DIR__. '/../Resources/datas/contenttype_*.yml');
        if (is_array($contentTypeDefinitions) && count($contentTypeDefinitions) > 0) {
            foreach ($contentTypeDefinitions as $contentTypeDefinition) {
                $contentTypeDefinition = Yaml::parse(file_get_contents($contentTypeDefinition));
                $contentTypeDefinition['contentTypeGroup'] = $contentTypeGroup;
                $this->contentType->add($contentTypeDefinition);
            }
        }

        return $contentTypes;
    }

    public function createMediaContentTypes(\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup)
    {
        $contentTypes = array();

        $contentTypeDefinitions = glob(__DIR__. '/../Resources/datas/mediacontenttype_*.yml');
        if (is_array($contentTypeDefinitions) && count($contentTypeDefinitions) > 0) {
            foreach ($contentTypeDefinitions as $contentTypeDefinition) {
                $contentTypeDefinition = Yaml::parse(file_get_contents($contentTypeDefinition));
                $contentTypeDefinition['contentTypeGroup'] = $contentTypeGroup;
                $this->contentType->add($contentTypeDefinition);
            }
        }

        return $contentTypes;
    }

    public function createContents($parentLocationID)
    {
        $contents = array();

        $contentDefinitions = glob(__DIR__. '/../Resources/datas/content_*.yml');
        if (is_array($contentDefinitions) && count($contentDefinitions) > 0) {
            foreach ($contentDefinitions as $contentDefinition) {
                $contentDefinition = Yaml::parse(file_get_contents($contentDefinition));
                $contentDefinition['parentLocationID'] = $parentLocationID;
                $contentAdded = $this->content->add($contentDefinition);
                $contents[] = $contentAdded;
            }
        }

        $modelsLocationID = false;
        $customersLocationID = false;
        foreach ($contents as $content) {
            /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType */
            $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
            switch ($contentType->identifier) {
                case 'edgar_ez_sb_modelsroot':
                    $modelsLocationID = $content->contentInfo->mainLocationId;
                    break;
                case 'edgar_ez_sb_customersroot':
                    $customersLocationID = $content->contentInfo->mainLocationId;
                    break;
                default:
                    break;
            }
        }

        return array(
            'contents' => $contents,
            'modelsLocationID' => $modelsLocationID,
            'customersLocationID' => $customersLocationID
        );
    }

    public function createMediaContents($parentLocationID)
    {
        $contents = array();

        $contentDefinitions = glob(__DIR__. '/../Resources/datas/mediacontent_*.yml');
        if (is_array($contentDefinitions) && count($contentDefinitions) > 0) {
            foreach ($contentDefinitions as $contentDefinition) {
                $contentDefinition = Yaml::parse(file_get_contents($contentDefinition));
                $contentDefinition['parentLocationID'] = $parentLocationID;
                $contentAdded = $this->content->add($contentDefinition);
                $contents[] = $contentAdded;
            }
        }

        $mediaModelsLocationID = false;
        $mediaCustomersLocationID = false;
        foreach ($contents as $content) {
            /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType */
            $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
            switch ($contentType->identifier) {
                case 'edgar_ez_sb_mediamodelsroot':
                    $mediaModelsLocationID = $content->contentInfo->mainLocationId;
                    break;
                case 'edgar_ez_sb_mediacustomersroot':
                    $mediaCustomersLocationID = $content->contentInfo->mainLocationId;
                    break;
                default:
                    break;
            }
        }

        return array(
            'contents' => $contents,
            'modelsLocationID' => $mediaModelsLocationID,
            'customersLocationID' => $mediaCustomersLocationID
        );
    }

    public function createUserGroups($parentLocationID)
    {
        $userGroupDefinition = Yaml::parse(file_get_contents(__DIR__. '/../Resources/datas/usergrouproot.yml'));
        $userGroupDefinition['parentLocationID'] = $parentLocationID;
        /** @var \eZ\Publish\API\Repository\Values\Content\Content $userGroup */
        $userGroup = $this->content->add($userGroupDefinition);

        $contents = array();
        $userGroupParenttLocationID = $userGroup->contentInfo->mainLocationId;

        $userGroupDefinitions = glob(__DIR__. '/../Resources/datas/usergroup_*.yml');
        if (is_array($userGroupDefinitions) && count($userGroupDefinitions) > 0) {
            foreach ($userGroupDefinitions as $userGroupDefinition) {
                $userGroupDefinition = Yaml::parse(file_get_contents($userGroupDefinition));
                $userGroupDefinition['parentLocationID'] = $userGroupParenttLocationID;
                /** @var \eZ\Publish\Core\REST\Client\Values\Content\Content $contentAdded */
                $contentAdded = $this->content->add($userGroupDefinition);
                $contents[] = $contentAdded;
            }
        }

        $userCreatorsLocationID = false;
        $userEditorsLocationID = false;
        foreach ($contents as $content) {
            /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType */
            $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
            switch ($contentType->identifier) {
                case 'user_group':
                    if ($content->contentInfo->name == 'Creators') {
                        $userCreatorsLocationID = $content->contentInfo->mainLocationId;
                    } else {
                        $userEditorsLocationID = $content->contentInfo->mainLocationId;
                    }
                    break;
                default:
                    break;
            }
        }

        return array(
            'userCreatorsLocationID' => $userCreatorsLocationID,
            'userEditorsLocationID' => $userEditorsLocationID
        );
    }
}
