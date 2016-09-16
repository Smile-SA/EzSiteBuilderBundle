<?php

namespace Smile\EzSiteBuilderBundle\Data\Site;

use Smile\EzSiteBuilderBundle\Values\Content\Site;

trait SiteDataTrait
{
    /** @var Site $site */
    protected $site;

    public function setSite(Site $site)
    {
        $this->site = $site;
    }

    public function getSite()
    {
        return $this->site;
    }
}
