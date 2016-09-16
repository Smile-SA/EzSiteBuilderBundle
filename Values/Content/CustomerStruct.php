<?php

namespace Smile\EzSiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class CustomerStruct extends ValueObject
{
    public $customerName;
    public $userFirstName;
    public $userLastName;
    public $userEmail;
}
