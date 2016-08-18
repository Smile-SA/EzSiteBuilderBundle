<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Class Validators
 *
 * @package EdgarEz\SiteBuilderBundle\Command
 */
class Validators
{
    /**
     * Validate location id input
     *
     * @param int $locationID ezplatform location id
     * @return bool|int false if not int, locationID if correct
     */
    public static function validateLocationID($locationID)
    {
        if (preg_match('/[^0-9]/', $locationID))
            return false;

        return $locationID;
    }

    /**
     * Validation string vendor name/customer name/model name ...
     *
     * @param string $vendorName vendor name
     * @return mixed exception if not valid, vendorName if correct
     */
    public static function validateVendorName($vendorName)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $vendorName)) {
            throw new \InvalidArgumentException('The vendor name contains invalid characters.');
        }

        return $vendorName;
    }

    /**
     * Validate string model name
     *
     * @param string $modelName model name
     * @return mixed exception if not valid, modelName if valid
     */
    public static function validateModelName($modelName)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $modelName)) {
            throw new \InvalidArgumentException('The model name contains invalid characters.');
        }

        return $modelName;
    }

    /**
     * validate system path
     *
     * @param string $dir system path where undle would be generated
     * @return string
     */
    public static function validateTargetDir($dir)
    {
        // add trailing / if necessary
        return '/' === substr($dir, -1, 1) ? $dir : $dir.'/';
    }

    /**
     * Validate user first name
     *
     * @param string $firstName user first name
     * @return mixed exception if not validated, first name otherwise
     */
    public static function validateFirstName($firstName)
    {
        if (!preg_match('/^[ \'a-zA-Z0-9-]*$/', $firstName)) {
            throw new \InvalidArgumentException('The first name contains invalid characters.');
        }

        return $firstName;
    }

    /**
     * Validate user last name
     *
     * @param string $lastName user last name
     * @return mixed exception if not validated, last name otherwise
     */
    public static function validateLastName($lastName)
    {
        if (!preg_match('/^[ \'a-zA-Z0-9-]*$/', $lastName)) {
            throw new \InvalidArgumentException('The last name contains invalid characters.');
        }

        return $lastName;
    }

    /**
     * Validate user email
     *
     * @param string $email user email
     * @return mixed user email
     * @throws InvalidArgumentException
     */
    public static function validateEmail($email)
    {
        if (!\ezcMailTools::validateEmailAddress($email)) {
            throw new InvalidArgumentException('email', $email, 'Validators');
        }

        return $email;
    }

    /**
     * Validate siteaccess host
     *
     * @param string $host siteaccess host
     * @return mixed host
     * @throws InvalidArgumentException
     */
    public static function validateHost($host)
    {
        if (!preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $host)
            && !preg_match("/^.{1,253}$/", $host)
            && !preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $host)
        ) {
            throw new InvalidArgumentException('host', $host, 'Validators');
        }

        return $host;
    }

    /**
     * Validate siteaccess suffix
     *
     * @param string $siteaccessSuffix
     * @return mixed siteaccess suffix
     * @throws InvalidArgumentException
     */
    public static function validateSiteaccessSuffix($siteaccessSuffix)
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_\x7f-\xff]*[a-zA-Z0-9]$/', $siteaccessSuffix)) {
            throw new InvalidArgumentException('siteaccessSuffix', $siteaccessSuffix, 'Validators');
        }

        return $siteaccessSuffix;
    }
}
