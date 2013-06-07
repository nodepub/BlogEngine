<?php

namespace NodePub\BlogEngine\Filter;

use NodePub\BlogEngine\Filter\FilterInterface;

/**
 * Chains other content filters so that they can run in sequence
 */
class FilterChain implements FilterInterface
{
    protected $filters;
    
    function __construct($filters = array())
    {
        $this->filters = $filters;
    }
    
    public function addFilter(FilterInterface $filter)
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
