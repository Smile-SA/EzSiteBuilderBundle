<?php

namespace EdgarEz\SiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class Site extends ValueObject
{
    protected $siteName;
    protected $model;
    protected $modelLocationID;
    protected $host;
    protected $mapuri;
    protected $suffix;
}