<?php

namespace EdgarEz\SiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class SiteStruct extends ValueObject
{
    public $languageCode;
    public $siteName;
    public $host;
    public $suffix;
}
