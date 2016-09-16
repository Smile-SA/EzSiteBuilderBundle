<?php

namespace Smile\EzSiteBuilderBundle\Data\Model;

use Smile\EzSiteBuilderBundle\Values\Content\ModelActivate;

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
