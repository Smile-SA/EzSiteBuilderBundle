<?php

namespace Smile\EzSiteBuilderBundle\DependencyInjection\Security\PolicyProvider;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\YamlPolicyProvider;

/**
 * Class SiteBuilderPolicyProvider
 * @package Smile\EzSiteBuilderBundle\DependencyInjection\Security\PolicyProvider
 */
class SiteBuilderPolicyProvider extends YamlPolicyProvider
{
    /** @var string $path bundle path */
    protected $path;

    /**
     * SiteBuilderPolicyProvider constructor.
     *
     * @param string $path bundle path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * prepend policies to eZ Platform policy configuration
     *
     * @return array list of policies.yml
     */
    public function getFiles()
    {
        return [$this->path . '/Resources/config/policies.yml'];
    }
}
