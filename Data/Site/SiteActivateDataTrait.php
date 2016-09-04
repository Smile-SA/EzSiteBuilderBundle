<?php

namespace EdgarEz\SiteBuilderBundle\Data\Site;

use EdgarEz\SiteBuilderBundle\Values\Content\SiteActivate;

trait SiteActivateDataTrait
{
    /**
     * @var SiteActivate
     */
    protected $siteActivate;

    /**
     * @param SiteActivate $siteActivate
     */
    public function setSiteActivate(SiteActivate $siteActivate)
    {
        $this->siteActivate = $siteActivate;
    }
}
