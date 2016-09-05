<?php

namespace EdgarEz\SiteBuilderBundle\Data\Model;

use EdgarEz\SiteBuilderBundle\Values\Content\ModelActivate;

trait ModelActivateDataTrait
{
    /**
     * @var ModelActivate
     */
    protected $modelActivate;

    /**
     * @param ModelActivate $modelActivate
     */
    public function setModelActivate(ModelActivate $modelActivate)
    {
        $this->modelActivate = $modelActivate;
    }
}