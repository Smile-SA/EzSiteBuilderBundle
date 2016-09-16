<?php

namespace Smile\EzSiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class User extends ValueObject
{
    protected $userType;
    protected $userFirstName;
    protected $userLastName;
    protected $userEmail;
}
