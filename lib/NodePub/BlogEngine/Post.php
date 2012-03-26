<?php

namespace NodePub\BlogEngine;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Simple Post model
 */
class Post
{
    public $title,
           $slug,
           $filename,
           $rawContent,
           $tags,
           $timestamp,
           $year,
           $month,
           $day,
           $prev,
           $next;
    
    protected $content,
              $contentFilter;

    function __construct(array $attributes = array())
    {
        // set default attributs
        $this->tags = new ArrayCollection();
        $this->timestamp = new \DateTime("now");
        
        // set given attributes
        foreach ($attributes as $name => $value) {
            $setter = 'set'.$name;
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            } else {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * DI setter for content filter object
     */
    public function setContentFilter($filter)
    {
        $this->contentFilter = $filter;
    }
    
    public function setRawContent($rawContent)
    {
        $this->rawContent = $rawContent;
    }
    
    /**
     * Gets the filtered content
     */
    public function getContent()
    {
        if (empty($this->content))
        {
            $this->filterContent();
        }
        
        return $this->content;
    }
    
    public function setTags(array $tags)
    {
        $this->tags = new ArrayCollection($tags);
    }
    
    public function addTag($name)
    {
        $this->tags->add($name);
    }
    
    /**
     * If a content filter is set, executes the filter on the raw content
     */
    protected function filterContent()
    {
        if (!is_null($this->contentFilter))
        {
            $this->content = $this->contentFilter->filter($this->rawContent);
        }
        else
        {
            $this->content = $this->rawContent;
        }
    }
}