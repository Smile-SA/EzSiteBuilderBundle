<?php

namespace EdgarEz\SiteBuilderBundle\Data\Install;

use EdgarEz\SiteBuilderBundle\Values\Content\Install;

trait InstallDataTrait
{
    /**
     * @var Install
     */
    protected $install;

    public function setInstall(Install $install)
    {
        $this->install = $install;
    }
}