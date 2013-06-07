<?php

namespace NodePub\BlogEngine\Event;

use Symfony\Component\EventDispatcher\Event;
use NodePub\BlogEngine\PostInterface;

class PostEvent extends Event
{
    protected $post;

    public function __construct(PostInterface $post)
    {
        $this->post = $post;
    }

    public function getPost()
    {
        return $this->post;
    }
}