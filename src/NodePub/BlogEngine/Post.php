<?php

namespace NodePub\BlogEngine;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Simple Post model
 */
class Post
{
    public $id,
           $title,
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

    function __construct($propertyObj = null)
    {
        // set default properties
        $this->tags = new ArrayCollection();
        $this->timestamp = new \DateTime("now");
        $this->year = $this->timestamp->format('Y');
        $this->month = $this->timestamp->format('m');
        $this->day = $this->timestamp->format('d');
        
        if (!is_object($propertyObj)) return;
        
        // set given properties
        foreach ($propertyObj as $key => $property) {
            $setter = 'set'.$key;
            if (method_exists($this, $setter)) {
                $this->{$setter}($property);
            } else {
                $this->{$key} = $property;
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
    
    public function setTags(array $tags = array())
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