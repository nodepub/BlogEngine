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
        
        # orders posts by date
        $files->sortByName();
        
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
    
    /**
     * Get the first 8 chars of the hashed permalink,
     * used as a post id
     */
    public function hashPermalink($permalink)
    {
        return substr(sha1($permalink), 0, 8);
    }
    
    /**
     * Creates an index of post metadata objects
     */
    public function getPostIndex()
    {
        if (is_null($this->postIndex)) {
            $posts = array();
            $files = $this->findFiles();

            foreach ($files as $fileinfo) {
                $contents = $this->readFile($fileinfo);
                $basename = $fileinfo->getBasename('.' . $this->sourceFileExtension);
                
                # @TODO: refactor to allow different filename/permalink schemas
                preg_match('/(\d{4})-(\d{2})-(\d{2})-(.+)/', $basename, $matches);
                
                $parser = new Parser($contents);
                $postInfo = (object) $parser->getMetadata();
                $postInfo->year = $matches[1];
                $postInfo->month = $matches[2];
                $postInfo->day = $matches[3];
                $postInfo->slug = $matches[4];
                $postInfo->timestamp = strtotime($postInfo->year.'-'.$postInfo->month.'-'.$postInfo->day);
                $postInfo->permalink = $postInfo->year.'/'.$postInfo->month.'/'.$postInfo->slug;
                $postInfo->id = $this->hashPermalink($postInfo->permalink);
                $postInfo->filepath = $fileinfo->getRealPath();
                $postInfo->filename = $fileinfo->getBasename();

                $posts[$postInfo->id] = $postInfo;
            }

            $this->postIndex = new ArrayCollection($posts);
        }
        
        return $this->postIndex;
    }

    /**
     * Gets the last N posts from the index and
     * optionally expands each to a full post object
     */
    public function findRecentPosts($limit, $page = 1, $expand = true)
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
    public function findById($id)
    {
        $index = $this->getPostIndex();
        $postMeta = $index->get($id);
        
        if ($postMeta) {
            $fileinfo = new \SplFileInfo($postMeta->filepath);
            $parser = new Parser($this->readFile($fileinfo));
            
            $post = new Post($postMeta);
            $post->setRawContent($parser->getContent());

            if (!is_null($this->contentFilter)) {
                $post->setContentFilter($this->contentFilter);
            }

            list($prev, $next) = $this->findPrevAndNextPosts($id);
            $post->prev = $prev;
            $post->next = $next;
            
            return $post;
        }
    }
    
    /**
     * Searches for a post by permalink
     */
    public function findByPermalink($permalink)
    {
        return $this->findById($this->hashPermalink($permalink));
    }

    /**
     * Given a post id, get the previous and next posts
     */
    public function findPrevAndNextPosts($id)
    {
        $prev = $next = null;
        $index = $this->getPostIndex();
        $postIds = $index->getKeys();

        for ($count = 0; $count < count($postIds); $count++) { 
            if ($postIds[$count] == $id) {
                if ($count !== 0) {
                    $prev = $index->get($postIds[$count - 1]);
                }

                if ($count !== count($postIds) - 1) {
                    $next = $index->get($postIds[$count + 1]);
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

        foreach ($postCollection as $id => $values) {
            $posts[] = $this->findById($id);
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
    
    public function renamePostFile(Post $post, $newPath = null)
    {
        $currentPath = $post->filepath;
        $newPath = isset($newPath) ? $newPath : $this->prepareFilePath($post, dirname($currentPath));
        
        try {
            rename($currentPath, $newPath);
            $post->filepath = $newPath;
            
            return $post;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Writes a Post to a file
     */
    public function savePost(Post $post, $fileContent)
    {
        if (isset($post->filepath)) {
            if ($this->hasRenamedFileProperties($post)) {
                $post = $this->renamePostFile($post);
            }
        } else {
            $post->filepath = $this->prepareFilePath($post);
        }
        
        $file = new \SplFileObject($post->filepath, 'w+');
        
        # write the content to the file
        # if file doesn't exist, it gets created
        try {
            $file->rewind();
            $file->fwrite($fileContent);
            $file->fflush();
            
            return $post;
        } catch (\Exception $e) {
            # @TODO
            return null;
        }
    }
    
    /**
     * Tests if any of the Post's properties that determine
     * filename have changed by comparing the current path
     * against the 
     */
    protected function hasRenamedFileProperties(Post $post)
    {
        return ($post->filepath != $this->prepareFilePath($post));
    }
    
    /**
     * Creates the full file pathname for a Post
     * 
     * @TODO: refactor to allow different filename/permalink schemas
     */
    protected function prepareFilePath(Post $post, $dir = null)
    {
        if (is_null($dir) || !is_dir($dir)) {
            $dir = $this->sourceDirs[0];
        }
        
        return sprintf('%s/%s-%s-%s.%s',
            $dir,
            $post->year,
            $post->month,
            $post->slug,
            $this->sourceFileExtension
        );
    }
    
    /**
     * Deletes a Post file
     */
    public function deletePost(Post $post)
    {
        if (is_file($post->filepath)) {
            try {
                unlink($post->filepath);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        
        return false;
    }
}
