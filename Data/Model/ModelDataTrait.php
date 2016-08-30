<?php

namespace EdgarEz\SiteBuilderBundle\Data\Model;

use EdgarEz\SiteBuilderBundle\Values\Content\Model;

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
