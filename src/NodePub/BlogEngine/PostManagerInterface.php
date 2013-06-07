<?php

namespace NodePub\BlogEngine;

use NodePub\BlogEngine\PostInterface;
use NodePub\BlogEngine\FilenameFormatter;
use NodePub\BlogEngine\PermalinkFormatter;

interface PostManagerInterface
{
    /**
     * Sets the filter passed to Posts for filtering content.
     * @see NodePub\BlogEngine\FilterMarkdown
     */
    public function setContentFilter($filter);

    /**
     * Sets the object that defines and builds permalink strings from Posts.
     */
    public function setPermalinkFormatter($formatter);

    /**
     * Gets the permalink formatter or instantiates a new one.
     */
    public function getPermalinkFormatter();
    
    /**
     * Calculates the number of pages given the number of posts per page.
     */
    public function getPageCount($postsPerPage);

    /**
     * Gets the last N posts from the index and
     * optionally expands each to a full post object
     *
     * @return array
     */
    public function findRecentPosts($length, $page = 1);
    
    /**
     * Searches the index for a matching post.
     * If found, it creates a new Post object with filtered content
     * 
     * @return mixed  found Post or null
     */
    public function findById(string $id);
    
    /**
     * Searches for a post by permalink
     *
     * @return mixed  found Post or null
     */
    public function findByPermalink(string $permalink);
    
    /**
     * Searches for a post by slug name
     *
     * @return mixed  found Post or null
     */
    public function findBySlug(string $slug);

    /**
     * Given a post id, get the previous and next posts
     * Because posts are displayed in reverse order,
     * Next is the newer, and Prev the older
     *
     * @return array
     */
    public function findPrevAndNext(PostInterface $post);

    /**
     * Returns an array of Posts that contain the given query params
     * 
     * @return array
     */
    public function filter(Array $query);

    /**
     * Sorts the post index into a new arrary
     * array(2013 => array(12 => array(post1, post2, ...)))
     * @return array
     */
    public function getArchive();
    
    /**
     * Returns all existing tags as an associative array
     * with the tag slug as the key and the tagging array as the value
     * e.g. array('foo' => array('Foo' => 7))
     * 
     * @return array
     */
    public function getTags();

    /**
     * Returns all existing tags as an associative array
     * with the tag name as the key and the number of taggings as the value
     * 
     * @return array
     */
    public function getTaggings();
    
    /**
     * Saves a Post
     */
    public function persist(PostInterface $post);
    
    /**
     * Deletes a Post
     */
    public function delete(PostInterface $post);
}
