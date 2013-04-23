<?php

namespace NodePub\BlogEngine;

use NodePub\BlogEngine\PostManager;
use NodePub\BlogEngine\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
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

    public function normalizeTag($tag)
    {
        $tags = $this->postManager->getTags();

        foreach ($tags as $tagArray) {
            if ($tag == $tagArray['slug'] || $tag == $tagArray['name']) {
                $normalizedTag = $tagArray['name'];

                return $tagArray['name'];
            }
        }

        return $tag;
    }

    public function postsAction($page)
    {
        return new Response(
            $this->templateEngine->render($this->config[Config::FRONTPAGE_TEMPLATE], array(
                'posts' => $this->postManager->findRecentPosts($this->config[Config::FRONTPAGE_POST_LIMIT], $page),
                'pageNumber' => $page,
                'pageCount' => $this->postManager->getPageCount($this->config[Config::FRONTPAGE_POST_LIMIT])
            ))
        );
    }

    public function postAction(Request $request, $year, $month, $slug)
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
        return new Response($this->templateEngine->render($this->config[Config::FRONTPAGE_TEMPLATE], array(
            'posts' => $this->postManager->filter(array('year' => $year))
        )));
    }

    public function monthIndexAction($year, $month)
    {
        return new Response($this->templateEngine->render($this->config[Config::FRONTPAGE_TEMPLATE], array(
            'posts' => $this->postManager->filter(array('year' => $year, 'month' => $month))
        )));
    }

    public function taggedPostsAction($tag)
    {
        $tag = $this->normalizeTag($tag);

        $posts = $this->postManager->filter(array('tags' => $tag));

        $response = new Response($this->templateEngine->render($this->config[Config::TAG_PAGE_TEMPLATE], array(
            'posts' => $posts,
            'tag' => $tag
        )));

        if (count($posts) === 0) {
            $response->setStatusCode(404);
        }

        return $response;
    }

    public function archiveAction($page)
    {
        return new Response(
            $this->templateEngine->render($this->config[Config::FRONTPAGE_TEMPLATE], array(
                'posts' => $this->postManager->findRecentPosts($this->config[Config::FRONTPAGE_POST_LIMIT], $page),
                'pageNumber' => $page,
                'pageCount' => $this->postManager->getPageCount($this->config[Config::FRONTPAGE_POST_LIMIT])
            ))
        );
    }

    public function rssAction()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/rss+xml; charset=utf-8');

        $response->setContent($this->templateEngine->render($this->config[Config::RSS_TEMPLATE], array(
            'posts' => $this->postManager->findRecentPosts($this->config[Config::RSS_POST_LIMIT])
        )));

        return $response;
    }
}