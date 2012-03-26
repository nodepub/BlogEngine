<?php

namespace NodePub\BlogEngine;

/**
 * Chains other content filters so that they can run in sequence
 */
class FilterChain
{
    protected $filters;
    
    function __construct($filters = array())
    {
        $this->filters = $filter;
    }
    
    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }
    
    public function filter($rawText)
    {
        $filteredText = $rawText;
        
        foreach ($this->filters as $filter)
        {
            $filteredText = $filter->filter($filteredText);
        }
        
        return $filteredText;
    }
}
