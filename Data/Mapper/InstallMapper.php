<?php

namespace EdgarEz\SiteBuilderBundle\Data\Mapper;

use EdgarEz\SiteBuilderBundle\Data\Install\InstallData;
use eZ\Publish\API\Repository\Values\ValueObject;
use EzSystems\RepositoryForms\Data\Mapper\FormDataMapperInterface;

class InstallMapper implements FormDataMapperInterface
{
    public function mapToFormData(ValueObject $install, array $params = [])
    {
        $data = new InstallData(['install' => $install]);

        return $data;
    }
}
