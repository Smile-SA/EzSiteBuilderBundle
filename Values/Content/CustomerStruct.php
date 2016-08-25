<?php

namespace EdgarEz\SiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class CustomerStruct extends ValueObject
{
    public $customerName;
    public $userFirstName;
    public $userLastName;
    public $userEmail;
}
