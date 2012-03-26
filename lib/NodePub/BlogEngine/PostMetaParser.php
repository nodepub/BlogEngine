<?php

namespace NodePub\BlogEngine;

use Symfony\Component\Yaml\Yaml;

/**
 * Parses Jekyll-style text files with Yaml metadata at the top
 * Splits the metadata from the rest of the content
 */
class PostMetaParser
{
    protected $metadata = array();
    protected $content = '';
    
    function __construct($source)
    {
        $this->parse($source);
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function getMetadata()
    {
        return $this->metadata;
    }
    
    /**
     * Splits the YAML metadata from the rest of the file
     * Converts YAML to an array
     *
     * @param string $source source text with Yaml metadata
     */
    public function parse($source)
    {
        $parts = preg_split('/[\n]*[-]{3}[\n]/', $source);
        
        if (count($parts) === 3) {
            $this->metadata = Yaml::parse($parts[1]);
            $this->content = $parts[2];
        } else {
            $this->content = $source;
        }
    }
}