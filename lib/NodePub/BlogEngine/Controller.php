<?php

namespace NodePub\BlogEngine;

use NodePub\BlogEngine\PostManager;
use NodePub\BlogEngine\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController
{
    protected $postManager;

    protected $templateEngine;

    protected $config;

    public function __construct(PostManager $postManager, $templateEngine, array $config)
    {
        $this->postManager = $postManager;
        $this->templateEngine = $templateEngine;
        $this->config = $config;
    }

    /**
     * @todo merge posts and pagedPosts, shouldn't need separate actions
     */
    public function postsAction()
    {
        return new Response(
            $this->templateEngine->render($this->config[Config::FRONTPAGE_TEMPLATE]), array(
                'posts' => $this->postManager->findRecentPosts($this->config[Config::FRONTPAGE_POST_LIMIT]),
                'pageNumber' => 1,
                'pageCount' => $this->postManager->getPageCount($this->config[Config::FRONTPAGE_POST_LIMIT])
            ))
        );
    }

    public function pagedPostsAction($page)
    {
        return new Response(
            $this->templateEngine->render($this->config[Config::FRONTPAGE_TEMPLATE], array(
                'posts' => $this->postManager->findRecentPosts($this->config['blog.frontpage.post_limit'], $page),
                'pageNumber' => $page,
                'pageCount' => $this->postManager->getPageCount($this->config['blog.frontpage.post_limit'])
            ))
        );
    }

    public function postAction($year, $month, $slug)
    {
        $permalink = sprintf("%s/%s/%s", $year, $month, $slug);
        $postInfo = $this->postManager->findByPermalink($permalink, false);

        if (!$postInfo) throw new \Exception('Page not found', 404);

        $response = new Response();
        $response->setLastModified($this->postManager->getModifiedDate($postInfo));

        # Check that the Response is not modified for the given Request
        if ($response->isNotModified($request)) {
            # return the 304 Response immediately
            return $response;
        }
        
        # get the expanded post
        $post = $this->postManager->findByPermalink($permalink);
        
        return new Response(
            $this->templateEngine->render($this->config['blog.permalink.template'], array(
                'node' => array('title' => $post->title),
                'post' => $post
            ))
        );
    }

    public function yearIndexAction($year)
    {
        return new Response($this->templateEngine->render($this->config['blog.frontpage.template'], array(
            'posts' => $this->postManager->filter(array('year' => $year))
        )));
    }

    public function monthIndexAction($year, $month)
    {
        return new Response($this->templateEngine->render($this->config['blog.frontpage.template'], array(
            'posts' => $this->postManager->filter(array('year' => $year, 'month' => $month))
        )));
    }

    public function taggedPostsAction($tag)
    {
        $tags = $this->postManager->getTags();
        $normalizedTag = $tag;

        // search
        foreach ($tags as $tagArray) {
            if ($tag == $tagArray['slug'] || $tag == $tagArray['name']) {
                $normalizedTag = $tagArray['name'];
                break;
            }
        }
    
        $posts = $this->postManager->filter(array('tags' => $normalizedTag));
        $response = new Response($this->templateEngine->render($this->config['blog.tag_page.template'], array(
            'posts' => $posts,
            'tag' => $normalizedTag
        )));
        if (count($posts) === 0) {
            $response->setStatusCode(404);
        }

        return $response;
    }

    public function archiveAction()
    {
        return new Response($this->templateEngine->render($this->config['blog.frontpage.template'], array(
            'page_1' => $this->postManager->findRecentPosts($this->config['blog.frontpage.post_limit'], 1),
            'page_2' => $this->postManager->findRecentPosts($this->config['blog.frontpage.post_limit'], 2),
            'page_3' => $this->postManager->findRecentPosts($this->config['blog.frontpage.post_limit'], 3),
            'page_4' => $this->postManager->findRecentPosts($this->config['blog.frontpage.post_limit'], 4),
            'pageCount' => $this->postManager->getPageCount($this->config['blog.frontpage.post_limit'])
        )));
    }

    public function rssAction()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/rss+xml; charset=utf-8');

        $response->setContent($this->templateEngine->render($this->config[Config::RSS_TEMPLATE], array(
            'posts' => $this->postManager->findRecentPosts(10)
        )));

        return $response;
    }
}