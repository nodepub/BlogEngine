<?php

namespace NodePub\BlogEngine;

/**
 * Formats Post permalinks. Allows for injecting a different permalink schema into PostManager.
 * Permalinks follow the format of: "year/month/slug" eg "2012/05/foo".
 */
class PermalinkFormatter
{
    /**
     * Creates the permalink for the given Post
     * @todo can't enforce a type, have to check the object's properties
     */
    public function getPermalink($post)
    {
        if (!isset($post->year) || !isset($post->month) || !isset($post->slug)) {
            throw new \Exception("Insufficient parameters for creating permalink.");
        }

        return $post->year . '/' . $post->month . '/' . $post->slug;
    }
}