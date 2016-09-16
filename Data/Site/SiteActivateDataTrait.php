<?php

namespace Smile\EzSiteBuilderBundle\Data\Site;

use Smile\EzSiteBuilderBundle\Values\Content\SiteActivate;

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
