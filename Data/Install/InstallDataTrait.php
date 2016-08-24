<?php

namespace EdgarEz\SiteBuilderBundle\Data\Install;

use EdgarEz\SiteBuilderBundle\Values\Content\Install;

/**
 * Class InstallDataTrait
 *
 * @package EdgarEz\SiteBuilderBundle\Data\Install
 */
trait InstallDataTrait
{
    /**
     * @var Install
     */
    protected $install;

    /**
     * @param Install $install
     */
    public function setInstall(Install $install)
    {
        $this->install = $install;
    }
}