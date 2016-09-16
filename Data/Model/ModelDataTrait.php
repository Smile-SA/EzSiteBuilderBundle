<?php

namespace Smile\EzSiteBuilderBundle\Data\Model;

use Smile\EzSiteBuilderBundle\Values\Content\Model;

trait ModelDataTrait
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @param Model $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }
}
