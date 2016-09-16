<?php

namespace Smile\EzSiteBuilderBundle\Data\Mapper;

use Smile\EzSiteBuilderBundle\Data\Site\SiteActivateData;
use eZ\Publish\API\Repository\Values\ValueObject;
use EzSystems\RepositoryForms\Data\Mapper\FormDataMapperInterface;

class SiteActivateMapper  implements FormDataMapperInterface
{
    /**
     * @param ValueObject $siteActivate
     * @param array       $params
     * @return SiteActivateData
     */
    public function mapToFormData(ValueObject $siteActivate, array $params = [])
    {
        $data = new SiteActivateData(['siteActivate' => $siteActivate]);

        return $data;
    }
}
