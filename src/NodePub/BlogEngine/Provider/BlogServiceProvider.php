<?php

namespace NodePub\BlogEngine\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\Definition\Processor;

use NodePub\BlogEngine\PostManager;
use NodePub\BlogEngine\Controller\BlogController;
use NodePub\BlogEngine\Twig\BlogTwigExtension;
use NodePub\BlogEngine\BlogConfiguration;

/**
 * Silex Service Provider that registers and configures an instance of the PostManager,
 * and defines routes and controller actions.
 * Uses constants in NodePub\BlogEngine\Config to manage configuration key names.
 * By setting separate config values on the $app container,
 * any of them can be overridden.
 */
class BlogServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // $app['np.blog.options'] = array(
        //     'templates' => array(
        //         'index' => '',
        //     ),
        //     'post_limits' => array(
        //         'rss' => 20,
        //         'recent_posts_widget' => 5,
        //     )
        // );
        
        $app['np.blog.options'] = array();
        $app['np.blog.theme.options'] = array();
        
        $app['np.blog.content_filter'] = $app->share(function($app) {
            $markdown = new \dflydev\markdown\MarkdownParser();
            return new \NodePub\BlogEngine\Filter\FilterMarkdown($markdown);
        });

        # Alias twig by wrapping it in a closure
        $app['np.blog.template.engine'] = function($app) {
            return $app['twig'];
        };
        
        if (isset($app['twig'])) {
            $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
                $twig->addExtension(new BlogTwigExtension(
                    $app['np.blog.post_manager'], 
                    $app['url_generator']
                ));

                $path = __DIR__ . '/../Resources/views';
                $app['twig.loader']->addLoader(new \Twig_Loader_Filesystem($path));

                return $twig;
            }));
        }

        $app['np.blog.mount_point'] = '/blog';
        $app['np.blog.permalink_format'] = '/{year}/{month}/{slug}';
        
        $app['np.blog.processed_options'] = $app->share(function($app) {
            $processor = new Processor();
            $config = new BlogConfiguration();
            
            // combine default blog settings with dynamic theme template settings
            return $processor->processConfiguration(
                $config,
                array($app['np.blog.options'], $app['np.blog.theme.options'])
            );
        });

        $app['np.blog.post_manager'] = $app->share(function($app) {
            $manager = new PostManager($app['np.blog.posts_dir']);
            $manager->setContentFilter($app['np.blog.content_filter']);
            $manager->setSourceFileExtension('txt');
            //$manager->setCacheDirectory($app['cache_dir']);
            $manager->setCacheDirectory(false);

            return $manager;
        });

        $app['blog.controller'] = $app->share(function($app) {
            return new BlogController(
                $app['blog.post_manager'],
                $app['np.blog.template.engine'],
                $app['np.blog.processed_options']
            );
        });
    }

    public function boot(Application $app)
    {
        $blog = $app['controllers_factory'];

        $blog->get('/', 'blog.controller:postsAction')
            ->bind('blog_get_posts')
            ->value('page', 1);

        $blog->get('/page/{page}', 'blog.controller:postsAction')
            ->assert('page', "\d")
            ->bind('blog_get_paged_posts');

        $blog->get($app['blog.permalink_format'], 'blog.controller:postAction')
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

        $blog->get('/archive', 'blog.controller:archiveAction')
            ->bind('blog_get_archive');

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
