<?php

namespace Smile\EzSiteBuilderBundle\Data\Mapper;

use Smile\EzSiteBuilderBundle\Data\Install\InstallData;
use eZ\Publish\API\Repository\Values\ValueObject;
use EzSystems\RepositoryForms\Data\Mapper\FormDataMapperInterface;

/**
 * Class InstallMapper
 *
 * @package Smile\EzSiteBuilderBundle\Data\Mapper
 */
class InstallMapper implements FormDataMapperInterface
{
    /**
     * @param ValueObject $install
     * @param array       $params
     * @return InstallData
     */
    public function mapToFormData(ValueObject $install, array $params = [])
    {
        $data = new InstallData(['install' => $install]);

        return $data;
    }
}
