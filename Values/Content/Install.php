<?php

namespace Smile\EzSiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Class Install
 *
 * @package Smile\EzSiteBuilderBundle\Values\Content
 */
class Install extends ValueObject
{
    protected $vendorName;
    protected $contentLocationID;
    protected $mediaLocationID;
    protected $userLocationID;
}
