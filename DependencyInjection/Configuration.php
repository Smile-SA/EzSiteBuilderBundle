<?php

namespace EdgarEz\SiteBuilderBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration extends SiteAccessConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('edgar_ez_site_builder');

        $systemNode = $this->generateScopeBaseNode($rootNode);
        $systemNode
            ->scalarNode('host')->isRequired()->end()
            ->scalarNode('sysadminemail')->isRequired()->end();

        return $treeBuilder;
    }
}
