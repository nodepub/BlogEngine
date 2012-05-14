<?php

namespace NodePub\BlogEngine;

/**
 * Defines the schema for Post filenames. Builds a filename from a Post's properties
 * as well as parsing an existing filename for a Post's properties.
 * Filenames follow the format of .../rootDir/year/year-month-day-slug.txt eg .../posts/2012/2012-12-21-apocalypse.txt
 */
class FilenameFormatter
{
	protected $rootDir;
	protected $sourceFileExtension;

	function __construct($rootDir, $extension)
    {
    	$this->rootDir = $rootDir;
    	$this->sourceFileExtension = $extension;
    }

    /**
     * Creates the full file pathname for a Post
     */
    public function getFilePath(Post $post, $dir = null)
    {   
    	if (is_null($dir)) {
    		$dir = $this->rootDir . '/' . $post->year;
    	}

        return sprintf('%s/%s-%s-%s-%s.%s',
            $dir,
            $post->year,
            $post->month,
            $post->day,
            $post->slug,
            $this->sourceFileExtension
        );
    }

    /**
     * 
     */
    public function getPostPropertiesFromFilename($filepath)
    {
    	$properties = array();
    	$basename = basename($filepath, '.' . $this->sourceFileExtension);
                
        preg_match('/(\d{4})-(\d{2})-(\d{2})-(.+)/', $basename, $matches);

        $properties['year']  = $matches[1];
        $properties['month'] = $matches[2];
        $properties['day']   = $matches[3];
        $properties['slug']  = $matches[4];

        return $properties;
    }
}