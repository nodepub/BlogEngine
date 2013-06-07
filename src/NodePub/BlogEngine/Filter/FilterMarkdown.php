<?php

namespace NodePub\BlogEngine\Filter;

use NodePub\BlogEngine\Filter\FilterInterface;

/**
 * Converts Markdown text into HTML
 */
class FilterMarkdown implements FilterInterface
{
    private $parser;
    
    function __construct($parser)
    {
        $this->parser = $parser;
    }
    
    public function filter($rawText)
    {
        return $this->parser->transform($rawText);
    }
}