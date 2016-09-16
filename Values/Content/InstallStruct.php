<?php

namespace Smile\EzSiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Class InstallStruct
 *
 * @package Smile\EzSiteBuilderBundle\Values\Content
 */
class InstallStruct extends ValueObject
{
    public $vendorName;
    public $contentLocationID;
    public $mediaLocationID;
    public $userLocationID;
}
