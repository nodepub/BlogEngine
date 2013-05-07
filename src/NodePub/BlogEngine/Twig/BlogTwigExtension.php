<?php

namespace NodePub\BlogEngine\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use NodePub\BlogEngine\PostManager;

class BlogTwigExtension extends \Twig_Extension
{
    private $urlGenerator,
            $postManager;

    protected $twigEnvironment;

    public function __construct(PostManager $postManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->postManager = $postManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->twigEnvironment = $environment;
    }

    public function getName()
    {
        return 'NodePubBlogEngine';
    }
    
    public function getFunctions()
    {
        return array(
            'blog_permalink'     => new \Twig_Function_Method($this, 'permalink'),
            'previous_page_link' => new \Twig_Function_Method($this, 'previousPageLink'),
            'blog_tag_links'     => new \Twig_Function_Method($this, 'tagLinks'),
            'blog_recent_posts'  => new \Twig_Function_Method($this, 'recentPosts'),
            'blog_archive'       => new \Twig_Function_Method($this, 'getArchive'),
            'blog_tags'          => new \Twig_Function_Method($this, 'getTags'),
        );
    }

    public function permalink($post)
    {
        return $this->urlGenerator->generate('blog_get_post', array(
            'year' => $post->year,
            'month' => $post->month,
            'slug' => $post->slug
        ));
    }

    public function previousPageLink($page)
    {
        $previousPage = $page - 1;
        if ($previousPage == 1) {
            $this->urlGenerator->generate('blog_get_posts');
        } else {
            $this->urlGenerator->generate('blog_get_paged_posts', array('page' => $previousPage));
        }
    }
    
    public function tagLinks($tags)
    {
        $links = array();
        
        foreach ($tags as $tag) {
            $tag = strtolower($tag);
            $href = $this->urlGenerator->generate('blog_get_tag_index', array('tag' => $tag));
            $links[]= sprintf('<a href="%s">%s</a>', $href, $tag);
        }
        
        return implode(', ', $links);
    }

    public function recentPosts($count)
    {
        return $this->postManager->findRecentPosts($count, 1, false);
    }

    public function getArchive()
    {
        return $this->postManager->getPostArchive();
    }

    /**
     * Returns an array of tags and their count
     */
    public function getTags()
    {
        $tags = $this->postManager->getTags();

        uasort($tags, function($a, $b) {

            if ($a['count'] == $b['count']) {
                return 0;
            }

            return ($a['count'] > $b['count']) ? -1 : 1;
        });

        return $tags;
    }
}
