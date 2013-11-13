<?php

namespace NodePub\BlogEngine;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class BlogConfiguration implements ConfigurationInterface
{
    // Internal templates
    const INDEX_TEMPLATE_NAME  = 'post_index.twig';
    const POST_TEMPLATE_NAME   = 'post.twig';
    const RSS_TEMPLATE_NAME    = 'rss.twig';
    
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('np.blog');
        
        $rootNode
            ->children()
                
                ->arrayNode('post_limits')
                    ->children()
                        ->integerNode('index_pages')
                            ->defaultValue(20)
                            ->min(1)
                        ->end()
                        ->integerNode('rss')
                            ->defaultValue(20)
                            ->min(1)
                        ->end()
                        ->integerNode('recent_posts_widget')
                            ->defaultValue(5)
                            ->min(1)
                        ->end()
                    ->end()
                ->end()
                
                ->arrayNode('templates')
                    ->children()
                        ->scalarNode('default')
                            ->defaultValue(self::POST_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('permalink')
                            ->defaultValue(self::POST_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('index')
                            ->defaultValue(self::INDEX_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('frontpage')
                            ->defaultValue(self::INDEX_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('archive')
                            ->defaultValue(self::INDEX_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('tag_page')
                            ->defaultValue(self::INDEX_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('category')
                            ->defaultValue(self::INDEX_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('rss')
                            ->defaultValue(self::RSS_TEMPLATE_NAME)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}