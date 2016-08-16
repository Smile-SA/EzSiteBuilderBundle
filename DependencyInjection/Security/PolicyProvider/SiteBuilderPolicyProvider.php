<?php

namespace EdgarEz\SiteBuilderBundle\DependencyInjection\Security\PolicyProvider;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\YamlPolicyProvider;

class SiteBuilderPolicyProvider extends YamlPolicyProvider
{
    public function getFiles()
    {
        return [__DIR__ . '/../../../Resources/config/policies.yml'];
    }
}
