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
     * @throws InvalidArgumentException
     */
    public static function validateLocationID($locationID)
    {
        if (!preg_match('/^[0-9]*/', $locationID)) {
            throw new InvalidArgumentException($locationID, 'Input is not a valid Location ID');
        }

        return $locationID;
    }

    /**
     * Validation string vendor name
     *
     * @param string $vendorName vendor name
     * @return mixed exception if not valid, vendorName if correct
     * @throws InvalidArgumentException
     */
    public static function validateVendorName($vendorName)
    {
        if (!preg_match('/^[A-Z][a-z]*$/', $vendorName)) {
            throw new InvalidArgumentException($vendorName, 'Input is not a valid Vendor name. Vendor name should contains only alphabetic characters, first uppercase; last lowercase.');
        }

        return $vendorName;
    }

    /**
     * Validation string customer name
     *
     * @param string $customerName customer name
     * @return mixed exception if not valid, customerName if correct
     * @throws InvalidArgumentException
     */
    public static function validateCustomerName($customerName)
    {
        if (!preg_match('/^[A-Z][a-zA-Z]*$/', $customerName)) {
            throw new InvalidArgumentException($customerName, 'Input is not a valid Customer name. Customer name should contains only alphabetic characters, first uppercase.');
        }

        return $customerName;
    }

    /**
     * Validate string model name
     *
     * @param string $modelName model name
     * @return mixed exception if not valid, modelName if valid
     * @throws InvalidArgumentException
     */
    public static function validateModelName($modelName)
    {
        if (!preg_match('/^[A-Z][a-zA-Z]*$/', $modelName)) {
            throw new InvalidArgumentException($modelName, 'Input is not a valid Model name. Model name should contains only alphabetic characters, first uppercase.');
        }

        return $modelName;
    }

    /**
     * Validation string site name
     *
     * @param string $siteName site name
     * @return mixed exception if not valid, siteName if correct
     * @throws InvalidArgumentException
     */
    public static function validateSiteName($siteName)
    {
        if (!preg_match('/^[A-Z][a-zA-Z]*$/', $siteName)) {
            throw new InvalidArgumentException($siteName, 'Input is not a valid Site name. Site name should contains only alphabetic characters, first uppercase.');
        }

        return $siteName;
    }

    /**
     * validate system path
     *
     * @param string $dir system path where undle would be generated
     * @return mixed Exception if not valid, system dir if correct
     * @throws InvalidArgumentException
     */
    public static function validateTargetDir($dir)
    {
        if (!preg_match('/^[a-zA-Z0-9_\-\/]*$/', $dir)) {
            throw new InvalidArgumentException($dir, 'Input is not a valid system dir.');
        }

        if (!is_writable($dir)) {
            throw new InvalidArgumentException($dir, 'System dir is not writable.');
        }

        // add trailing / if necessary
        return '/' === substr($dir, -1, 1) ? $dir : $dir.'/';
    }

    /**
     * Validate user first name
     *
     * @param string $firstName user first name
     * @return mixed exception if not validated, first name otherwise
     * @throws InvalidArgumentException
     */
    public static function validateFirstName($firstName)
    {
        if (!preg_match('/^[ \'a-zA-Z0-9-]*$/', $firstName)) {
            throw new InvalidArgumentException($firstName, 'Input is not a valid user first name.');
        }

        return $firstName;
    }

    /**
     * Validate user last name
     *
     * @param string $lastName user last name
     * @return mixed exception if not validated, last name otherwise
     * @throws InvalidArgumentException
     */
    public static function validateLastName($lastName)
    {
        if (!preg_match('/^[ \'a-zA-Z0-9-]*$/', $lastName)) {
            throw new InvalidArgumentException($lastName, 'Input is not a valid user last name.');
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
            throw new InvalidArgumentException($email, 'Input is not a valid email.');
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
            throw new InvalidArgumentException($host, 'Input is not a valid host.');
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
        if (!preg_match('/^[a-zA-Z0-9_\x7f-\xff]*$/', $siteaccessSuffix)) {
            throw new InvalidArgumentException($siteaccessSuffix, 'Input is not a host suffix.');
        }

        return $siteaccessSuffix;
    }
}
