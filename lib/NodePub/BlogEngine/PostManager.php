<?php

namespace NodePub\BlogEngine;

use Symfony\Component\Finder\Finder;
use Doctrine\Common\Collections\ArrayCollection;
use NodePub\BlogEngine\Post;
use NodePub\BlogEngine\PostMetaParser as Parser;

class PostManager
{
    protected $sourceDirs;
    protected $postIndex;
    protected $tags;
    protected $contentFilter;
    protected $sourceFileExtension;
  
    function __construct($sourceDirs)
    {
        # initialize params with defaults
        $this->sourceDirs = array();
        $this->sourceFileExtension = 'txt';
        
        if (is_array($sourceDirs)) {
            foreach ($sourceDirs as $dir) {
                $this->addSource($dir);
            }
        } elseif (is_string($sourceDirs)) {
            $this->addSource($sourceDirs);
        }
    }
    
    /**
     * Adds a directory to the array of sources that will
     * be searched for post files
     */
    public function addSource($sourcePath)
    {
        if (is_dir($sourcePath)) {
            $this->sourceDirs[] = $sourcePath;
        } else {
            throw new \Exception(sprintf('Source path "%s" is not a directory', $sourcePath));
        }
    }

    /**
     * Set the filter passed to Posts for filtering content
     * @see NodePub\BlogEngine\FilterMarkdown
     */
    public function setContentFilter($filter)
    {
        $this->contentFilter = $filter;
    }
    
    /**
     * Set the extension used for searching for post files
     */
    public function setSourceFileExtension($ext)
    {
        $this->sourceFileExtension = $ext;
    }
    
    /**
     * Finds all text files in the configured posts dir(s).
     * Returns array of SplFileInfo objects.
     */
    protected function findFiles()
    {
        $files = Finder::create()
            ->files()
            ->name('*.' . $this->sourceFileExtension)
            ;
        
        # add all source paths to the finder
        foreach ($this->sourceDirs as $dir) {
           $files->in($dir);
        }
        
        return $files;
    }
    
    /**
     * Returns the contents of a file
     */
    protected function readFile(\SplFileInfo $fileinfo)
    {
        $file = $fileinfo->openFile();
        $file->rewind();
        $contents = '';
        while (!$file->eof()) {
            $contents.= $file->fgets();
        }
        
        return $contents;
    }
    
    public function getPostIndex()
    {
        if (is_null($this->postIndex)) {
            $posts = array();
            $files = $this->findFiles();

            foreach ($files as $fileinfo) {
                $contents = $this->readFile($fileinfo);
                $parser = new Parser($contents);
                $metadata = $parser->getMetadata();

                $basename = $fileinfo->getBasename('.' . $this->sourceFileExtension);
                
                # @TODO: refactor to allow different filename/permalink schemas
                preg_match('/(\d{4})-(\d{2})-(\d{2})-(.+)/', $basename, $matches);

                $metadata['year'] = $matches[1];
                $metadata['month'] = $matches[2];
                $metadata['day'] = $matches[3];
                $metadata['slug'] = $matches[4];
                $metadata['timestamp'] = strtotime($metadata['year'].'-'.$metadata['month'].'-'.$metadata['day']);

                $metadata['permalink'] = $metadata['year'].'/'.$metadata['month'].'/'.$metadata['slug'];
                $metadata['filename'] = $fileinfo->getRealPath();

                $posts[$metadata['permalink']] = $metadata;
            }
            
            # sort posts in incremental order
            asort($posts);

            $this->postIndex = new ArrayCollection($posts);
        }
        
        return $this->postIndex;
    }

    /**
     * Gets the last N posts from the index and
     * optionally expands each to a full post object
     */
    public function getRecentPosts($limit, $page = 1, $expand = true)
    {
        $posts = $this->getPostIndex();
        $offset = $limit * $page;

        if ($offset > $posts->count()) {
            $recentPosts = $posts;
        } else {
            $recentPosts = $posts->slice(-$offset, $limit);
        }
        
        if ($expand) {
            $recentPosts = $this->expandPosts($recentPosts);
        }
        
        if ($recentPosts instanceOf ArrayCollection) {
            $recentPosts = $recentPosts->toArray();
        }

        return array_reverse($recentPosts);
    }
    
    /**
     * Searches the index for a matching post.
     * If found, it creates a new Post object with filtered content
     * 
     * @return Post
     */
    public function getPost($permalink)
    {
        $index = $this->getPostIndex();
        $postMeta = $index->get($permalink);
        
        if ($postMeta) {
            $fileinfo = new \SplFileInfo($postMeta['filename']);
            $parser = new Parser($this->readFile($fileinfo));
            
            $post = new Post($postMeta);
            $post->setRawContent($parser->getContent());

            if (!is_null($this->contentFilter)) {
                $post->setContentFilter($this->contentFilter);
            }

            list($prev, $next) = $this->getPrevAndNextPosts($permalink);
            $post->prev = $prev;
            $post->next = $next;
            
            return $post;
        }
    }

    public function getPrevAndNextPosts($permalink)
    {
        $prev = $next = null;
        $index = $this->getPostIndex();
        $permalinkIndex = $index->getKeys();

        for ($count = 0; $count < count($permalinkIndex); $count++) { 
            if ($permalinkIndex[$count] == $permalink) {
                if ($count !== 0) {
                    $prev = $index->get($permalinkIndex[$count - 1]);
                }

                if ($count !== count($permalinkIndex) - 1) {
                    $next = $index->get($permalinkIndex[$count + 1]);
                }

                break;
            }
        }

        return array($prev, $next);
    }

    /**
     * Takes an ArrayCollection of post metas and expands each into a 
     * full Post object with parsed content
     */
    public function expandPosts(ArrayCollection $postCollection)
    {
        $posts = array();

        foreach ($postCollection as $slug => $values) {
            $posts[] = $this->getPost($slug);
        }

        return $posts;
    }

    /**
     * Returns an array of Posts that contain the given query params
     * 
     * @return array
     */
    public function filter(Array $query)
    {
        $filteredPosts = $this->getPostIndex();

        foreach ($query as $key => $value) {
            $filteredPosts = $filteredPosts->filter(function($postMeta) use($key, $value) {
                if (!array_key_exists($key, $postMeta)) return false;

                if (is_array($postMeta[$key])) {
                    return (in_array($value, $postMeta[$key]));
                }

                return $value == $postMeta[$key];
            });
        }
        
        return array_reverse($this->expandPosts($filteredPosts));
    }
    
    /**
     * Returns all existing tags as an associative array
     * with the tag name as the key and the number of taggings as the value
     * 
     * @return array
     */
    public function getTags()
    {
        if (is_null($this->tags))
        {
            $this->tags = array();

            foreach ($this->getPostIndex() as $postMeta) {
                if (!array_key_exists('tags', $postMeta)) continue;
                $taggings = $postMeta['tags'];
                foreach ($taggings as $tag)
                {
                    if (array_key_exists($tag, $this->tags))
                    {
                        $this->tags[$tag]++;
                    }
                    else
                    {
                        $this->tags[$tag] = 1;
                    }
                }
            }
        }
        
        return $this->tags;
    }
    
    /**
     * Writes a Post to a file
     */
    public function savePost(Post $post, $fileContent)
    {
        $fileName = $this->getFileNameOrCreate($post);
        
        # @TODO: need to allow for saving renamed posts
        
        $file = new \SplFileObject($fileName, 'w+');
        
        # write the content to the file
        # if file doesn't exist, it gets created
        try {
            $file->rewind();
            $file->fwrite($fileContent);
            $file->fflush();
            
            return $post;
        } catch (\Exception $e) {
            # @TODO
        }
    }
    
    /**
     * Gets a post's filename if it exists,
     * otherwise creates the filename
     */
    protected function getFileNameOrCreate($post)
    {
        if (isset($post->filename)) {
            $filenName = $post->filename;
        } else {
            # @TODO: refactor to allow different filename/permalink schemas
            $fileName = sprintf('%s/%s-%s-%s.%s',
                $this->sourceDirs[0],
                $post->year,
                $post->month,
                $post->slug,
                $this->sourceFileExtension
            );
        }
        
        return $fileName;
    }
    
    /**
     * Deletes a Post file
     */
    public function deletePost(Post $post)
    {
        $postFile = '';
        if (is_file($postFile)) {
            try {
                unlink($postFile);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        
        return false;
    }
}
