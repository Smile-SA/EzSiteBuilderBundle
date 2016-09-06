<?php

namespace EdgarEz\SiteBuilderBundle\Data\Mapper;

use EdgarEz\SiteBuilderBundle\Data\User\UserData;
use eZ\Publish\API\Repository\Values\ValueObject;
use EzSystems\RepositoryForms\Data\Mapper\FormDataMapperInterface;

class UserMapper implements FormDataMapperInterface
{
    public function mapToFormData(ValueObject $user, array $params = [])
    {
        $data = new UserData(['user' => $user]);

        return $data;
    }
}
