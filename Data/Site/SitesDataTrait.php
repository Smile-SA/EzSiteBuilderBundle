<?php

namespace Smile\EzSiteBuilderBundle\Data\Site;

use Smile\EzSiteBuilderBundle\Values\Content\Sites;

trait SitesDataTrait
{
    protected $sites;

    public function setlistSites(Sites $sites)
    {
        $this->sites = $sites;
    }
}
