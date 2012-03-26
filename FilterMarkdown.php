<?php

namespace NodePub\BlogEngine;

/**
 * Converts Markdown text into HTML
 */
class FilterMarkdown
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