<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EzSystems\PlatformUIBundle\Controller\Controller;

class ModelController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }
}
