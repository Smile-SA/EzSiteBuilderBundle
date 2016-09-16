<?php

namespace Smile\EzSiteBuilderBundle\Data\Mapper;

use Smile\EzSiteBuilderBundle\Data\Site\SiteData;
use EzSystems\RepositoryForms\Data\Mapper\FormDataMapperInterface;
use eZ\Publish\API\Repository\Values\ValueObject;

class SiteMapper implements FormDataMapperInterface
{
    public function mapToFormData(ValueObject $site, array $params = [])
    {
        $data = new SiteData(['site' => $site]);

        return $data;
    }
}
