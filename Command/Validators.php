<?php
/**
 * Created by PhpStorm.
 * User: emdro
 * Date: 02/08/2016
 * Time: 15:49
 */

namespace EdgarEz\SiteBuilderBundle\Command;


class Validators
{
    public static function validateLocationID($locationID)
    {
        if (preg_match('/[^0-9]/', $locationID))
            return false;

        return $locationID;
    }

    public static function validateVendorName($vendorName)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $vendorName)) {
            throw new \InvalidArgumentException('The vendor name contains invalid characters.');
        }

        return $vendorName;
    }

    public static function validateModelName($modelName)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $modelName)) {
            throw new \InvalidArgumentException('The model name contains invalid characters.');
        }

        return $modelName;
    }

    public static function validateTargetDir($dir)
    {
        // add trailing / if necessary
        return '/' === substr($dir, -1, 1) ? $dir : $dir.'/';
    }
}
