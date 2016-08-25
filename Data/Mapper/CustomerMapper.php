<?php

namespace EdgarEz\SiteBuilderBundle\Data\Mapper;

use EdgarEz\SiteBuilderBundle\Data\Customer\CustomerData;
use EzSystems\RepositoryForms\Data\Mapper\FormDataMapperInterface;
use eZ\Publish\API\Repository\Values\ValueObject;

class CustomerMapper implements FormDataMapperInterface
{
    public function mapToFormData(ValueObject $customer, array $params = [])
    {
        $data = new CustomerData(['customer' => $customer]);

        return $data;
    }
}
