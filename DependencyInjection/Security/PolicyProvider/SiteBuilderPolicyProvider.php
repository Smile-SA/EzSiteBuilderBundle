<?php

namespace EdgarEz\SiteBuilderBundle\DependencyInjection\Security\PolicyProvider;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\YamlPolicyProvider;

/**
 * Class SiteBuilderPolicyProvider
 * @package EdgarEz\SiteBuilderBundle\DependencyInjection\Security\PolicyProvider
 */
class SiteBuilderPolicyProvider extends YamlPolicyProvider
{
    /**
     * prepend policies to eZ Platform policy configuration
     *
     * @return array list of policies.yml
     */
    public function getFiles()
    {
        return [__DIR__ . '/../../../Resources/config/policies.yml'];
    }
}
