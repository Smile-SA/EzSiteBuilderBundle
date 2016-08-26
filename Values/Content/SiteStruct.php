<?php

namespace EdgarEz\SiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class SiteStruct extends ValueObject
{
    public $siteName;
    public $model;
    public $modelLocationID;
    public $host;
    public $mapuri;
    public $suffix;
}
