<?php

namespace EdgarEz\SiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class Customer extends ValueObject
{
    protected $customerName;
    protected $userFirstName;
    protected $userLastName;
    protected $userEmail;
}
