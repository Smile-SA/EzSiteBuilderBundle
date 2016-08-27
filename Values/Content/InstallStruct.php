<?php

namespace EdgarEz\SiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Class InstallStruct
 *
 * @package EdgarEz\SiteBuilderBundle\Values\Content
 */
class InstallStruct extends ValueObject
{
    public $vendorName;
    public $contentLocationID;
    public $mediaLocationID;
    public $userLocationID;
}
