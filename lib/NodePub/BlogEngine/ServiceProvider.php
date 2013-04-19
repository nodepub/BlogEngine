<?php

namespace NodePub\BlogEngine;

use Silex\Application;
use Silex\ServiceProviderInterface;
use NodePub\BlogEngine\PostManager;
use NodePub\BlogEngine\Controller;
use NodePub\BlogEngine\Twig\TwigExtension;
use NodePub\BlogEngine\Config as Blog;

/**
 * Silex Service Provider that registers and configures an instance of the PostManager,
 * and defines routes and controller actions.
 * Uses constants in NodePub\BlogEngine\Config to manage configuration key names.
 * By setting separate config values on the $app container,
 * any of them can be overridden.
 */
class ServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app[Blog::FRONTPAGE_POST_LIMIT] = 10;
        $app[Blog::FRONTPAGE_TEMPLATE]   = '@default/blog_index.twig';
        $app[Blog::PERMALINK_TEMPLATE]   = '@default/blog_post.twig';
        $app[Blog::DEFAULT_TEMPLATE]     = '@default/blog_post.twig';
        $app[Blog::TAG_PAGE_TEMPLATE]    = '@default/blog_index.twig';
        $app[Blog::CATEGORY_TEMPLATE]    = '@default/blog_index.twig';
        $app[Blog::RSS_POST_LIMIT]       = 20;
        $app[Blog::RSS_TEMPLATE]         = '@default/rss.twig';
        $app[Blog::RECENT_POSTS_LIMIT]   = 5;

        $app[Blog::CONTENT_FILTER] = $app->share(function($app) {
            $markdown = new \dflydev\markdown\MarkdownParser();
            return new \NodePub\BlogEngine\FilterMarkdown($markdown);
        });

        # Alias twig by wrapping it in a closure
        $app[Blog::TEMPLATE_ENGINE] = function($app) {
            return $app['twig'];
        };
        
        if (isset($app['twig'])) {
            $app['twig']->addExtension(new TwigExtension($app['url_generator']));
        }

        $app['blog.mount_point'] = '/blog';

        $app['blog.post_manager'] = $app->share(function($app) {
            $manager = new PostManager($app[Blog::POSTS_DIR]);
            $manager->setContentFilter($app[Blog::CONTENT_FILTER]);
            $manager->setSourceFileExtension('txt');
            //$manager->setCacheDirectory($app['cache_dir']);
            $manager->setCacheDirectory(false);

            return $manager;
        });

        $app['blog.controller'] = $app->share(function($app) {
            $config = array_replace(
                Blog::getDefaults(),
                array(
                    Blog::FRONTPAGE_POST_LIMIT => $app[Blog::FRONTPAGE_POST_LIMIT],
                    Blog::FRONTPAGE_TEMPLATE   => $app[Blog::FRONTPAGE_TEMPLATE],
                    Blog::PERMALINK_TEMPLATE   => $app[Blog::PERMALINK_TEMPLATE],
                    Blog::DEFAULT_TEMPLATE     => $app[Blog::DEFAULT_TEMPLATE],
                    Blog::TAG_PAGE_TEMPLATE    => $app[Blog::TAG_PAGE_TEMPLATE],
                    Blog::CATEGORY_TEMPLATE    => $app[Blog::CATEGORY_TEMPLATE],
                    Blog::RSS_POST_LIMIT       => $app[Blog::RSS_POST_LIMIT],
                    Blog::RSS_TEMPLATE         => $app[Blog::RSS_TEMPLATE],
                    Blog::RECENT_POSTS_LIMIT   => $app[Blog::RECENT_POSTS_LIMIT],
                    Blog::POSTS_DIR            => $app[Blog::POSTS_DIR]
                )
            );

            return new Controller($app['blog.post_manager'], $app[Blog::TEMPLATE_ENGINE], $config);
        });
    }

    public function boot(Application $app)
    {
        $blog = $app['controllers_factory'];

        $blog->get('/', 'blog.controller:postsAction')
            ->bind('blog_get_posts');

        $blog->get('/page/{page}', 'blog.controller:pagedPostsAction')
            ->assert('page', "\d")
            ->bind('blog_get_paged_posts');

        $blog->get('/{year}/{month}/{slug}', 'blog.controller:postAction')
            ->assert('year', "\d\d\d\d")
            ->assert('month', "\d\d")
            ->bind('blog_get_post');

        $blog->get('/{year}', 'blog.controller:yearIndexAction')
            ->assert('year', "\d\d\d\d")
            ->bind('blog_get_year_index');

        $blog->get('/{year}/{month}', 'blog.controller:monthIndexAction')
            ->assert('year', "\d\d\d\d")
            ->assert('month', "\d\d")
            ->bind('blog_get_month_index');

        $blog->get('/tags/{tag}', 'blog.controller:taggedPostsAction')
            ->bind('blog_get_tag_index');

        $blog->get('/archive', 'blog.controller:getArchiveAction')
            ->assert('page', "\d")
            ->bind('blog_get_post_archive');

        $blog->get('/rss', 'blog.controller:rssAction')
            ->bind('blog_rss');

        // mount blog controllers
        $app->mount($app['blog.mount_point'], $blog);

        // create an index redirect
        $app->get($app['blog.mount_point'], function() use ($app) {
            return $app->redirect($app['url_generator']->generate('blog_get_posts'));
        });
    }
}
