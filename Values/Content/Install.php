<?php

namespace EdgarEz\SiteBuilderBundle\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Class Install
 *
 * @package EdgarEz\SiteBuilderBundle\Values\Content
 */
class Install extends ValueObject
{
    protected $vendorName;
    protected $contentLocationID;
    protected $mediaLocationID;
    protected $userLocationID;
}
