<?php

namespace Smile\EzSiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class Site extends ValueObject
{
    protected $languageCode;
    protected $host;
    protected $suffix;
}
