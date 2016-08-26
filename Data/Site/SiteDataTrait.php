<?php

namespace EdgarEz\SiteBuilderBundle\Data\Site;

use EdgarEz\SiteBuilderBundle\Values\Content\Site;

trait SiteDataTrait
{
    /** @var Site $site */
    protected $site;

    public function setSite(Site $site)
    {
        $this->site = $site;
    }
}
