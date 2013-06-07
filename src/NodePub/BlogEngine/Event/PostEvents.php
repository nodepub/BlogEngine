<?php

namespace NodePub\BlogEngine\Event;

final class PostEvents
{
    /**
     * The np.blog.pre_create event is thrown each time a post is about to be persisted for the first time.
     *
     * The event listener receives an
     * NodePub\BlogEngine\Event\PostEvent instance.
     *
     * @var string
     */
    const PRE_CREATE = 'np.blog.pre_create';

    /**
     * The np.blog.create event is thrown each time a post is persisted for the first time.
     *
     * The event listener receives an
     * NodePub\BlogEngine\Event\PostEvent instance.
     *
     * @var string
     */
    const CREATE = 'np.blog.create';

    /**
     * The np.blog.create event is thrown each time a post is about to be persisted.
     *
     * The event listener receives an
     * NodePub\BlogEngine\Event\PostEvent instance.
     *
     * @var string
     */
    const PRE_PERSIST = 'np.blog.pre_persist';

    /**
     * The np.blog.create event is thrown each time a post persisted.
     *
     * The event listener receives an
     * NodePub\BlogEngine\Event\PostEvent instance.
     *
     * @var string
     */
    const PERSIST = 'np.blog.persist';

    const PRE_EDIT = 'np.blog.pre_edit';
    const EDIT = 'np.blog.edit';

    const PRE_DESTROY = 'np.blog.pre_destroy';
    const DESTROY = 'np.blog.destroy';
}