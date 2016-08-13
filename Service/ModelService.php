<?php

namespace EdgarEz\SiteBuilderBundle\Service;

use EdgarEz\ToolsBundle\Service\Content;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\URLAliasService;
use Symfony\Component\Yaml\Yaml;

class ModelService
{
    private $content;
    private $urlAliasService;
    private $locationService;

    public function __construct(
        URLAliasService $urlAliasService,
        LocationService $locationService,
        Content $content
    )
    {
        $this->urlAliasService = $urlAliasService;
        $this->locationService = $locationService;
        $this->content = $content;
    }

    public function createModelContent($modelsLocationID, $modelName)
    {
        $returnValue = array();

        $contentDefinition = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/datas/modelcontent.yml'));
        $contentDefinition['parentLocationID'] = $modelsLocationID;
        $contentDefinition['fields']['title']['value'] = $modelName;
        $contentAdded = $this->content->add($contentDefinition);

        $contentLocation = $this->locationService->loadLocation($contentAdded->contentInfo->mainLocationId);
        $contentPath = $this->urlAliasService->reverseLookup($contentLocation, $contentAdded->contentInfo->mainLanguageCode)->path;
        $returnValue['excludeUriPrefixes'] = trim($contentPath, '/') . '/';
        $returnValue['modelLocationID'] = $contentAdded->contentInfo->mainLocationId;

        return $returnValue;
    }

    public function createMediaModelContent($mediaModelsLocationID, $modelName)
    {
        $contentDefinition = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/datas/mediamodelcontent.yml'));
        $contentDefinition['parentLocationID'] = $mediaModelsLocationID;
        $contentDefinition['fields']['title']['value'] = $modelName;
        $contentAdded = $this->content->add($contentDefinition);

        return $contentAdded->contentInfo->mainLocationId;
    }
}
