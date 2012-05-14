<?php

namespace NodePub\BlogEngine\Tests;

use NodePub\BlogEngine\PostManager;

class PostManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $postManager;
    protected $fixturesDir;

    public function setUp()
    {
        $this->fixturesDir = __DIR__ . '/../../../fixtures';
        $this->postManager = new PostManager($this->fixturesDir);
    }

    public function testContructorSingleSourceDir()
    {
        $postManager = new PostManager($this->fixturesDir);
        $this->assertInstanceOf('NodePub\BlogEngine\PostManager', $postManager);
    }

    public function testContructorMultipleSourceDirs()
    {
        $postManager = new PostManager(array($this->fixturesDir));
        $this->assertInstanceOf('NodePub\BlogEngine\PostManager', $postManager);
    }

    public function testGetPermalinkFormatter()
    {
        $this->assertInstanceOf(
            'NodePub\BlogEngine\PermalinkFormatter',
            $this->postManager->getPermalinkFormatter()
        );
    }

    public function testGetFilnameFormatter()
    {
        $this->assertInstanceOf(
            'NodePub\BlogEngine\FilenameFormatter',
            $this->postManager->getFilenameFormatter()
        );
    }
    
    public function testGetPostIndex()
    {
        // if (is_null($this->postIndex)) {
        //     $posts = array();
        //     $files = $this->findFiles();

        //     foreach ($files as $fileinfo) {
        //         $contents = $this->readFile($fileinfo);
        //         $basename = $fileinfo->getBasename('.' . $this->sourceFileExtension);
                
        //         // preg_match('/(\d{4})-(\d{2})-(\d{2})-(.+)/', $basename, $matches);
        //         // $parser = new Parser($contents);
        //         // $postInfo = (object) $parser->getMetadata();
        //         // $postInfo->year = $matches[1];
        //         // $postInfo->month = $matches[2];
        //         // $postInfo->day = $matches[3];
        //         // $postInfo->slug = $matches[4];

        //         $parser = new Parser($contents);
        //         $postInfo = $parser->getMetadata();
        //         $filenameProperties = $this->filenameFormatter->getPostPropertiesFromFilename($fileinfo->getRealPath());
        //         $postInfo = (object) array_merge($postInfo, $filenameProperties);

        //         $postInfo->timestamp = strtotime($postInfo->year.'-'.$postInfo->month.'-'.$postInfo->day);
        //         $postInfo->permalink = $this->permalinkFormatter->getPermalink($postInfo);
        //         $postInfo->id = $this->hashPermalink($postInfo->permalink);
        //         $postInfo->filepath = $fileinfo->getRealPath();
        //         $postInfo->filename = $fileinfo->getBasename();

        //         $posts[$postInfo->id] = $postInfo;
        //     }

        //     $this->postIndex = new ArrayCollection($posts);
        // }
        
        // return $this->postIndex;
    }


    public function testFindRecentPosts()
    {
        // $posts = $this->getPostIndex();
        // $offset = $limit * $page;

        // if ($offset > $posts->count()) {
        //     $recentPosts = $posts;
        // } else {
        //     $recentPosts = $posts->slice(-$offset, $limit);
        // }
        
        // if ($expand) {
        //     $recentPosts = $this->expandPosts($recentPosts);
        // }
        
        // if ($recentPosts instanceOf ArrayCollection) {
        //     $recentPosts = $recentPosts->toArray();
        // }

        // return array_reverse($recentPosts);
    }
    
    public function testFindById()
    {
        // $index = $this->getPostIndex();
        // $postMeta = $index->get($id);
        
        // if ($postMeta) {
        //     $fileinfo = new \SplFileInfo($postMeta->filepath);
        //     $parser = new Parser($this->readFile($fileinfo));
            
        //     $post = new Post($postMeta);
        //     $post->setRawContent($parser->getContent());

        //     if (!is_null($this->contentFilter)) {
        //         $post->setContentFilter($this->contentFilter);
        //     }

        //     list($prev, $next) = $this->findPrevAndNextPosts($id);
        //     $post->prev = $prev;
        //     $post->next = $next;
            
        //     return $post;
        // }
    }
    
    public function testFindByPermalink()
    {
        //return $this->findById($this->hashPermalink($permalink));
    }

    public function testFindPrevAndNextPosts()
    {
        // $prev = $next = null;
        // $index = $this->getPostIndex();
        // $postIds = $index->getKeys();

        // for ($count = 0; $count < count($postIds); $count++) { 
        //     if ($postIds[$count] == $id) {
        //         if ($count !== 0) {
        //             $prev = $index->get($postIds[$count - 1]);
        //         }

        //         if ($count !== count($postIds) - 1) {
        //             $next = $index->get($postIds[$count + 1]);
        //         }

        //         break;
        //     }
        // }

        // return array($prev, $next);
    }

    /**
     * Takes an ArrayCollection of post metas and expands each into a 
     * full Post object with parsed content
     */
    // public function expandPosts(ArrayCollection $postCollection)
    // {
    //     $posts = array();

    //     foreach ($postCollection as $id => $values) {
    //         $posts[] = $this->findById($id);
    //     }

    //     return $posts;
    // }


    public function testFilter()
    {
        // $filteredPosts = $this->getPostIndex();

        // foreach ($query as $key => $value) {
        //     $filteredPosts = $filteredPosts->filter(function($postMeta) use($key, $value) {
        //         if (!array_key_exists($key, $postMeta)) return false;

        //         if (is_array($postMeta[$key])) {
        //             return (in_array($value, $postMeta[$key]));
        //         }

        //         return $value == $postMeta[$key];
        //     });
        // }
        
        // return array_reverse($this->expandPosts($filteredPosts));
    }
    
    /**
     * Returns all existing tags as an associative array
     * with the tag name as the key and the number of taggings as the value
     * 
     * @return array
     */
    public function testGetTags()
    {
        // if (is_null($this->tags))
        // {
        //     $this->tags = array();

        //     foreach ($this->getPostIndex() as $postMeta) {
        //         if (!array_key_exists('tags', $postMeta)) continue;
        //         $taggings = $postMeta['tags'];
        //         foreach ($taggings as $tag) {
        //             if (array_key_exists($tag, $this->tags)) {
        //                 $this->tags[$tag]++;
        //             }
        //             else {
        //                 $this->tags[$tag] = 1;
        //             }
        //         }
        //     }
        // }
        
        // return $this->tags;
    }
    
    public function testRenamePostFile()
    {
        // $this->dispatch(self::EVENT_PRE_MOVE, $post);

        // $currentPath = $post->filepath;
        // $newPath = isset($newPath) ? $newPath : $this->filenameFormatter->getFilePath($post, dirname($currentPath));
        
        // try {
        //     rename($currentPath, $newPath);
        //     $post->filepath = $newPath;

        //     $this->dispatch(self::EVENT_MOVE, $post);
            
        //     return $post;
        // } catch (\Exception $e) {
        //     return $e;
        // }
    }
    

    public function testSavePost()
    {
        // $this->dispatch(self::EVENT_PRE_SAVE, $post);

        // if (isset($post->filepath)) {
        //     if ($this->hasRenamedFileProperties($post)) {
        //         $post = $this->renamePostFile($post);
        //         if ($post instanceof \Exception) {
        //             return $post;
        //         }
        //     }
        // } else {
        //     $post->filepath = $this->filenameFormatter->getFilePath($post);
        // }
        
        // try {
        //     # write the content to the file
        //     # if file doesn't exist, it gets created
        //     $result = file_put_contents($post->filepath, $fileContent);
            
        //     if (false === $result) {
        //         return new \Exception('File could not be written');
        //     } else {
        //         # TODO: find better way to manage adding and removing from the index
        //         $post->permalink = $this->permalinkFormatter->getPermalink($post);
        //         $post->id = $this->hashPermalink($post->permalink);
        //         $post->filename = basename($post->filepath);

        //         $this->dispatch(self::EVENT_SAVE, $post);
                
        //         return $post;
        //     }
        // } catch (\Exception $e) {
        //     return $e;
        // }
    }
    

    public function testDeletePost()
    {
    //     $this->dispatch(self::EVENT_PRE_DELETE, $post);

    //     if (is_file($post->filepath)) {
    //         try {
    //             unlink($post->filepath);
    //             $this->dispatch(self::EVENT_DELETE, $post);
    //             return true;
    //         } catch (\Exception $e) {
    //             return false;
    //         }
    //     }
        
    //     return false;
    }
}
