<?php

namespace EdgarEz\SiteBuilderBundle\Data\Site;

use EdgarEz\SiteBuilderBundle\Values\Content\Sites;

trait SitesDataTrait
{
    protected $sites;

    public function setlistSites(Sites $sites)
    {
        $this->sites = $sites;
    }
}
