<?php

namespace NodePub\BlogEngine;

class Config
{
    const TEMPLATE_ENGINE      = 'blog.template.engine';
    const CONTENT_FILTER       = 'blog.content_filter';
    const FRONTPAGE_POST_LIMIT = 'blog.frontpage.post_limit';

    // Layout Types
    const FRONTPAGE_TEMPLATE   = 'blog.frontpage.template';
    const PERMALINK_TEMPLATE   = 'blog.permalink.template';
    const DEFAULT_TEMPLATE     = 'blog.default.template';
    const ARCHIVE_TEMPLATE     = 'blog.archive.template';
    const TAG_PAGE_TEMPLATE    = 'blog.tag_page.template';
    const CATEGORY_TEMPLATE    = 'blog.category_page.template';

    const RSS_POST_LIMIT       = 'blog.rss.post_limit';
    
    const RECENT_POSTS_LIMIT   = 'blog.recent_posts.post_limit';
    const POSTS_DIR            = 'blog.posts_dir';

    // Internal templates
    const INDEX_TEMPLATE       = 'post_index.twig';
    const POST_TEMPLATE        = 'post.twig';
    const RSS_TEMPLATE         = 'blog.rss.template';
    
    function __construct()
    {
    }

    public static function getDefaults()
    {
        return array(
            self::TEMPLATE_ENGINE      => null,
            self::CONTENT_FILTER       => null,
            self::FRONTPAGE_POST_LIMIT => 20,
            self::INDEX_TEMPLATE       => 'post_index.twig',
            self::POST_TEMPLATE        => 'post.twig',
            self::RSS_TEMPLATE         => 'rss.twig',

            self::FRONTPAGE_TEMPLATE   => 'post_index.twig',
            self::PERMALINK_TEMPLATE   => 'post.twig',
            self::DEFAULT_TEMPLATE     => 'post.twig',
            self::ARCHIVE_TEMPLATE     => 'post_archive.twig',
            self::TAG_PAGE_TEMPLATE    => 'post_index.twig',
            self::CATEGORY_TEMPLATE    => 'post_index.twig',
            self::RSS_POST_LIMIT       => 20,
            
            self::RECENT_POSTS_LIMIT   => 5,
            self::POSTS_DIR            => null
        );
    }
}
