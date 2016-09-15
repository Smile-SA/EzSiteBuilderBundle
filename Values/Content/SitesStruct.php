<?php

namespace EdgarEz\SiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class SitesStruct extends ValueObject
{
    public $model;
    public $siteName;
    /** @var SiteStruct[] $listSites */
    public $listSites;
    public $modelLocationID;
    public $customerName;
    public $customerContentLocationID;
    public $customerMediaLocationID;
}
