<?php

namespace NodePub\BlogEngine;

use Symfony\Component\EventDispatcher\Event;
use NodePub\BlogEngine\Post;

class PostEvent extends Event
{
    protected $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function getPost()
    {
        return $this->post;
    }
}