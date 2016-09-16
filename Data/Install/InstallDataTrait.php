<?php

namespace Smile\EzSiteBuilderBundle\Data\Install;

use Smile\EzSiteBuilderBundle\Values\Content\Install;

/**
 * Class InstallDataTrait
 *
 * @package Smile\EzSiteBuilderBundle\Data\Install
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
