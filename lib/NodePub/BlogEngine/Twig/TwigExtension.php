<?php

namespace NodePub\BlogEngine\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwigExtension extends \Twig_Extension
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getName()
    {
        return 'NodePubBlogEngine';
    }
    
    public function getFunctions()
    {
        return array(
            'permalink'   => new \Twig_Function_Method($this, 'permalink'),
            'previous_page_link' => new \Twig_Function_Method($this, 'previousPageLink'),
            'tag_links'   => new \Twig_Function_Method($this, 'tagLinks'),
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
}
