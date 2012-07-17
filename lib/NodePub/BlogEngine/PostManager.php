<?php

namespace NodePub\BlogEngine;

use Symfony\Component\Finder\Finder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Collections\ArrayCollection;
use NodePub\BlogEngine\Post;
use NodePub\BlogEngine\PostMetaParser as Parser;
use NodePub\BlogEngine\FilenameFormatter;
use NodePub\BlogEngine\PermalinkFormatter;
use NodePub\BlogEngine\PostEvent;

class PostManager
{
    const EVENT_PRE_CREATE = 'npblog.pre_create_file';
    const EVENT_CREATE     = 'npblog.create_file';
    const EVENT_PRE_SAVE   = 'npblog.pre_save_file';
    const EVENT_SAVE       = 'npblog.save_file';
    const EVENT_PRE_MOVE   = 'npblog.pre_move_file';
    const EVENT_MOVE       = 'npblog.move_file';
    const EVENT_PRE_DELETE = 'npblog.pre_delete_file';
    const EVENT_DELETE     = 'npblog.delete_file';

    const INDEX_CACHE_FILE = 'npblogPostIndex.json';

    protected $sourceDirs;
    protected $postIndex;
    protected $tags;
    protected $taggings;
    protected $contentFilter;
    protected $sourceFileExtension;
    protected $permalinkFormatter;
    protected $filenameFormatter;
    protected $eventDispatcher;
    protected $postIndexCacheFile;
  
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

        # set default cache file location
        $this->setCacheDirectory($this->sourceDirs[0]);
    }
    
    /**
     * Adds a directory to the array of sources that will
     * be searched for post files.
     */
    public function addSource($sourcePath)
    {
        if (is_link($sourcePath)) {
            $this->addSource(realpath($sourcePath));
            return;
        }
        
        if (is_dir($sourcePath)) {
            $this->sourceDirs[] = $sourcePath;
        } else {
            throw new \Exception(sprintf('Source path "%s" is not a readable directory', $sourcePath));
        }
    }

    /**
     * Sets the filter passed to Posts for filtering content.
     * @see NodePub\BlogEngine\FilterMarkdown
     */
    public function setContentFilter($filter)
    {
        $this->contentFilter = $filter;
    }
    
    /**
     * Sets the extension used for searching for post files.
     */
    public function setSourceFileExtension($ext)
    {
        $this->sourceFileExtension = $ext;
    }

    /**
     * Sets the object that defines and builds permalink strings from Posts.
     */
    public function setPermalinkFormatter($formatter)
    {
        $this->permalinkFormatter = $formatter;
    }

    /**
     * Gets the permalink formatter or instantiates a new one.
     */
    public function getPermalinkFormatter()
    {
        if (is_null($this->permalinkFormatter)) {
            $this->permalinkFormatter = new PermalinkFormatter();
        }

        return $this->permalinkFormatter;
    }

    /**
     * Sets the object used for formatting Post filenames.
     */
    public function setFilenameFormatter($formatter)
    {
        $this->filenameFormatter = $formatter;
    }

    /**
     * Gets the filename formatter or instantiates a new one.
     */
    public function getFilenameFormatter()
    {
        if (is_null($this->filenameFormatter)) {
            $this->filenameFormatter = new FilenameFormatter(
                $this->sourceDirs[0],
                $this->sourceFileExtension
            );
        }

        return $this->filenameFormatter;
    }

    /**
     * Sets the cache directory used to create the cache file path.
     * If false it turns caching off.
     * @param mixed 
     */
    public function setCacheDirectory($dir)
    {
        if (false !== $dir) {
            $this->postIndexCacheFile = $dir.'/'.self::INDEX_CACHE_FILE;
        } else {
            $this->postIndexCacheFile = false;
        }
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * Dispatches a PostEvent if an eventDispatcher is set.
     */
    protected function dispatch($eventName, Post $post)
    {
        if (isset($this->eventDispatcher)) {
            $event = new PostEvent($post);
            $this->eventDispatcher->dispatch($eventName, $event);
        }
    }
    
    /**
     * Finds all files in the configured posts dir(s)
     * with the configured file extension.
     *
     * @return array SplFileInfo objects
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

        # reverse order so that newest are first
        return array_reverse(iterator_to_array($files));
    }
    
    /**
     * Returns the contents of a file.
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
     * Get the first 8 chars of the hashed permalink to use as an id.
     */
    public function hashPermalink($permalink)
    {
        return substr(sha1($permalink), 0, 8);
    }
    
    /**
     * Returns the index of post metadata objects.
     * Creates a new index unless it already exists or if
     * $forceRefresh is true.
     *
     * @param boolean $forceRefresh
     * @return ArrayCollection
     */
    public function getPostIndex($forceRefresh = false)
    {
        if (is_null($this->postIndex)) {

            if (false === $this->postIndexCacheFile) {
                // bypass caching
                $this->postIndex = $this->createIndex();
            } else {
                $cache = new FileCache($this->postIndexCacheFile);
                if (!$forceRefresh && $cache->isFresh(filemtime($this->sourceDirs[0].'/.'))) {
                    $this->postIndex = new ArrayCollection($cache->load());
                } else {
                    $this->postIndex = $this->createIndex();
                    $cache->dump($this->postIndex->toArray());
                }
            }
        }
        
        return $this->postIndex;
    }
    
    /**
     * Re-creates the post index and cache
     * @return ArrayCollection
     */
    public function refreshPostIndex()
    {
        unset($this->postIndex);
        return $this->getPostIndex(true);
    }

    /**
     * Traverses post files to create the index of post meta data
     *
     * @return ArrayCollection  Unexpanded post meta objects
     */
    protected function createIndex()
    {
        $posts = array();
        $files = $this->findFiles();

        foreach ($files as $fileinfo) {
            $contents = $this->readFile($fileinfo);
            $basename = $fileinfo->getBasename('.' . $this->sourceFileExtension);
            $parser = new Parser($contents);
            $postInfo = $parser->getMetadata();
            $filenameProperties = $this->getFilenameFormatter()->getPostPropertiesFromFilename($fileinfo->getRealPath());
            
            $postInfo = (object) array_merge($postInfo, $filenameProperties);
            $postInfo->timestamp = strtotime($postInfo->year.'-'.$postInfo->month.'-'.$postInfo->day);
            $postInfo->permalink = $this->getPermalinkFormatter()->getPermalink($postInfo);
            $postInfo->id = $this->hashPermalink($postInfo->permalink);
            $postInfo->filepath = $fileinfo->getRealPath();
            $postInfo->filename = $fileinfo->getBasename();

            $posts[$postInfo->id] = $postInfo;
        }

        return new ArrayCollection($posts);
    }

    /**
     * Calculates the number of pages given the number of posts per page.
     */
    public function getPageCount($postsPerPage)
    {
        $postCount = $this->getPostIndex()->count();
        $pageCount = 1;

        if ($postCount == 0 || $postsPerPage == 0) {
            $pageCount = 0;
        }

        if ($postCount > $postsPerPage) {
            // subtract the remainder to get an inter value
            $pageCount = ($postCount - ($postCount % $postsPerPage)) / $postsPerPage;
            // add a page if there is a remainder
            if ($postCount % $postsPerPage !== 0) {
                $pageCount++;
            }
        }

        return $pageCount;
    }

    /**
     * Gets the last N posts from the index and
     * optionally expands each to a full post object
     *
     * @return array
     */
    public function findRecentPosts($length, $page = 1, $expand = true)
    {
        $posts = $this->getPostIndex();
        $offset = 0;

        if ($page > 1) {
            $offset += $length * ($page - 1);
        }

        $recentPosts = new ArrayCollection($posts->slice($offset, $length));

        if ($expand) {
            $recentPosts = $this->expandPosts($recentPosts);
        }
        
        if ($recentPosts instanceOf ArrayCollection) {
            $recentPosts = $recentPosts->toArray();
        }

        return $recentPosts;
    }
    
    /**
     * Searches the index for a matching post.
     * If found, it creates a new Post object with filtered content
     * 
     * @return mixed  found Post or null
     */
    public function findById($id, $expand = true)
    {
        $index = $this->getPostIndex();
        $postMeta = (object) $index->get($id);
        
        if ($postMeta) {

            if ($expand === false) {
                # return unexpanded post
                return $postMeta;
            }

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
     *
     * @return mixed  found Post or null
     */
    public function findByPermalink($permalink, $expand = true)
    {
        return $this->findById($this->hashPermalink($permalink), $expand);
    }
    
    /**
     * Searches for a post by slug name
     *
     * @return mixed  found Post or null
     */
    public function findBySlug($slug, $expand = true)
    {
        $filteredPosts = $this->getPostIndex()->filter(function($postInfo) use($slug) {
            if (!isset($postInfo->slug)) return false;

            return $slug == $postInfo->slug;
        });
        
        return $this->findById($filteredPosts->first()->id, $expand);
    }

    /**
     * Given a post id, get the previous and next posts
     *
     * @return array
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
     * @todo return ArrayCollection?
     * @return array
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
                $match = false;
                if (isset($postMeta->$key)) {
                    if (is_array($postMeta->$key)) {
                        $match = (in_array($value, $postMeta->$key));
                    } else {
                        $match = ($value == $postMeta->$key);
                    }
                }

                return $match;
            });
        }

        return $this->expandPosts($filteredPosts);
    }
    
    /**
     * Returns all existing tags as an associative array
     * with the tag slug as the key and the tagging array as the value
     * e.g. array('foo' => array('Foo' => 7))
     * 
     * @return array
     */
    public function getTags()
    {
        if (is_null($this->tags)) {
            $this->tags = array();
            $taggings = $this->getTaggings();
            foreach ($taggings as $tagName => $tagCount) {
                $tagSlug = str_replace(' ', '-', strtolower($tagName));
                $this->tags[$tagSlug] = array($tagName => $tagCount);
            }
        }
        
        return $this->tags;
    }

    /**
     * Returns all existing tags as an associative array
     * with the tag name as the key and the number of taggings as the value
     * 
     * @return array
     */
    public function getTaggings()
    {
        if (is_null($this->taggings)) {
            $this->taggings = array();

            foreach ($this->getPostIndex() as $postMeta) {
                if (!isset($postMeta->tags)) continue;
                foreach ($postMeta->tags as $tag) {
                    if (array_key_exists($tag, $this->taggings)) {
                        $this->taggings[$tag]++;
                    } else {
                        $this->taggings[$tag] = 1;
                    }
                }
            }
        }
        
        return $this->taggings;
    }

    /**
     * Returns the modified date of a post file
     * @return DateTime
     */
    public function getModifiedDate($post)
    {
        $date = new \DateTime();
        if (isset($post->filepath) && is_file($post->filepath)) {
            $date->setTimestamp(filemtime($post->filepath));
        }

        return $date;
    }
    
    /**
     * Renames a Post file
     */
    public function renamePostFile(Post $post, $newPath = null)
    {
        $this->dispatch(self::EVENT_PRE_MOVE, $post);

        $currentPath = $post->filepath;
        $newPath = isset($newPath) ? $newPath : $this->getFilenameFormatter()->getFilePath($post, dirname($currentPath));
        
        try {
            rename($currentPath, $newPath);
            $post->filepath = $newPath;

            $this->dispatch(self::EVENT_MOVE, $post);
            
            return $post;
        } catch (\Exception $e) {
            return $e;
        }
    }
    
    /**
     * Writes a Post to a file
     */
    public function savePost(Post $post, $fileContent)
    {

        $this->dispatch(self::EVENT_PRE_SAVE, $post);

        if (isset($post->filepath)) {
            if ($this->hasRenamedFileProperties($post)) {
                $post = $this->renamePostFile($post);
                if ($post instanceof \Exception) {
                    return $post;
                }
            }
        } else {
            $post->filepath = $this->getFilenameFormatter()->getFilePath($post);
        }
        
        try {
            # write the content to the file
            # if file doesn't exist, it gets created
            $result = file_put_contents($post->filepath, $fileContent);
            
            if (false === $result) {
                return new \Exception('File could not be written');
            } else {
                # TODO: find better way to manage adding and removing from the index
                $post->permalink = $this->getPermalinkFormatter()->getPermalink($post);
                $post->id = $this->hashPermalink($post->permalink);
                $post->filename = basename($post->filepath);

                $this->dispatch(self::EVENT_SAVE, $post);
                
                return $post;
            }
        } catch (\Exception $e) {
            return $e;
        }
    }
    
    /**
     * Tests if any of the Post's properties that determine filename have changed
     */
    protected function hasRenamedFileProperties(Post $post)
    {
        return ($post->filepath != $this->getFilenameFormatter()->getFilePath($post));
    }
    
    /**
     * Deletes a Post file
     */
    public function deletePost(Post $post)
    {
        $this->dispatch(self::EVENT_PRE_DELETE, $post);

        if (is_file($post->filepath)) {
            try {
                unlink($post->filepath);
                $this->dispatch(self::EVENT_DELETE, $post);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        
        return false;
    }
}
