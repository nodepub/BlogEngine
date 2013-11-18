<?php

namespace NodePub\BlogEngine;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class BlogConfiguration implements ConfigurationInterface
{
    // Internal templates
    const TEMPLATE_NAMESPACE   = 'np_blog';
    const INDEX_TEMPLATE_NAME  = '@np_blog/post_index.twig';
    const POST_TEMPLATE_NAME   = '@np_blog/post.twig';
    const RSS_TEMPLATE_NAME    = '@np_blog/rss.twig';
    
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('blog');
        
        $rootNode
            ->children()
                
                ->arrayNode('post_limits')
                    ->addDefaultsIfNotSet()
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
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('index')
                            ->defaultValue(self::INDEX_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('post')
                            ->defaultValue(self::POST_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('tags')
                            ->defaultValue(self::INDEX_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('category')
                            ->defaultValue(self::INDEX_TEMPLATE_NAME)
                        ->end()
                        ->scalarNode('archive')
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