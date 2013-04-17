<?php

namespace NodePub\BlogEngine;

class Config
{
    const TEMPLATE_ENGINE      = 'blog.template.engine';
    const CONTENT_FILTER       = 'blog.content_filter';
    const FRONTPAGE_POST_LIMIT = 'blog.frontpage.post_limit';
    const FRONTPAGE_TEMPLATE   = 'blog.frontpage.template';
    const PERMALINK_TEMPLATE   = 'blog.permalink.template';
    const DEFAULT_TEMPLATE     = 'blog.default.template';
    const TAG_PAGE_TEMPLATE    = 'blog.tag_page.template';
    const CATEGORY_TEMPLATE    = 'blog.category_page.template';
    const RSS_POST_LIMIT       = 'blog.rss.post_limit';
    const RSS_TEMPLATE         = 'blog.rss.template';
    const RECENT_POSTS_LIMIT   = 'blog.recent_posts.post_limit';
    const POSTS_DIR            = 'blog.posts_dir';
    
    function __construct()
    {
    }

    public static function getDefaults()
    {
        return array(
            self::TEMPLATE_ENGINE      => null,
            self::CONTENT_FILTER       => null,
            self::FRONTPAGE_POST_LIMIT => 20,
            self::FRONTPAGE_TEMPLATE   => 'blog.twig',
            self::PERMALINK_TEMPLATE   => 'post.twig',
            self::DEFAULT_TEMPLATE     => 'post.twig',
            self::TAG_PAGE_TEMPLATE    => 'blog.twig',
            self::CATEGORY_TEMPLATE    => 'blog.twig',
            self::RSS_POST_LIMIT       => 20,
            self::RSS_TEMPLATE         => 'rss.twig',
            self::RECENT_POSTS_LIMIT   => 5,
            self::POSTS_DIR            => null
        );
    }
}
