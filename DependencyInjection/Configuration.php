<?php

namespace Kitpages\UserGeneratedBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;


/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kitpages_user_generated');

        $this->addCommentSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Parses the kitpages_user_generated sections
     * Example for yaml driver:
     * kitpages_user_generated:
     *     comment:
     *         default_status: "validated"
     *
     * @param ArrayNodeDefinition $node
     * @return void
     */
    private function addCommentSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode("comment")
                    ->children()
                        ->scalarNode('default_status')->defaultValue("validated")->end()
                        ->scalarNode('from_email')->cannotBeEmpty()->isRequired()->end()
                        ->booleanNode('use_recaptcha')->cannotBeEmpty()->defaultValue(false)->end()
                        ->arrayNode('admin_email_list')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->beforeNormalization()
                                ->ifTrue(function($v){ return !is_array($v); })
                                ->then(function($v){ return array($v); })
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()

                    ->end()
                ->end()
            ->end();
    }

}
