<?php

namespace EdgarEz\SiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class Sites extends ValueObject
{
    protected $model;
    /** @var Site[] $listSites */
    protected $listSites;
    protected $modelLocationID;
    protected $customerName;
    protected $customerContentLocationID;
    protected $customerMediaLocationID;

    public function getlisteSites()
    {
        return $this->listSites;
    }
}
