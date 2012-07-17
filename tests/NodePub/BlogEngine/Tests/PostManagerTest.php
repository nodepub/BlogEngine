<?php

namespace NodePub\BlogEngine\Tests;

use NodePub\BlogEngine\PostManager;
use Doctrine\Common\Collections\ArrayCollection;

class PostManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $postManager;
    protected $fixturesDir;

    protected function setUp()
    {
        $this->fixturesDir = __DIR__ . '/../../../fixtures';
        $this->postManager = new PostManager($this->fixturesDir);
        $this->postIndex = new ArrayCollection($this->getMockPostsArray());
    }

    protected function getMockPostsArray()
    {
        $post0 = new \stdClass();
        $post0->tags = array('Foo', 'Bar Bas', 'Snurp');
        $post0->foo = 'bar';

        $post1 = new \stdClass();
        $post1->tags = array('Dynamo', 'Goo');

        $post2 = new \stdClass();
        $post2->tags = array('Foo', 'Bar Bas', 'Snork');

        $post3 = new \stdClass();
        $post3->tags = array('Foo', 'Larp');

        $post4 = new \stdClass();
        $post4->noTags = 'This item has no tags';

        return array($post0, $post1, $post2, $post3, $post4);
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
    
    // public function testGetPostIndex()
    // {
    // }


    // public function testFindRecentPosts()
    // {
    // }
    
    // public function testFindById()
    // {
    // }
    
    // public function testFindByPermalink()
    // {
    // }

    // public function testFindPrevAndNextPosts()
    // {
    // }

    // public function testExpandPosts()
    // {
    // }

    /**
     * @dataProvider dataProviderTestFilter
     */
    public function testFilter($expected, $filterArray)
    {
        $postManager = $this->getMockBuilder('NodePub\BlogEngine\PostManager')
            ->setMethods(array('getPostIndex', 'expandPosts'))
            ->disableOriginalConstructor()
            ->getMock();

        $postManager->expects($this->once())
            ->method('getPostIndex')
            ->will($this->returnValue($this->postIndex));

        $postManager->expects($this->once())
            ->method('expandPosts')
            ->will($this->returnArgument(0));

        $this->assertEquals($expected, $postManager->filter($filterArray)->toArray());
    }

    public function dataProviderTestFilter()
    {
        $posts = $this->getMockPostsArray();

        return array(
            array(array($posts[0]), array('tags' => 'Snurp')),
            array(array($posts[0]), array('foo' => 'bar')),
            // filtered array keeps original keys
            array(array(0 => $posts[0], 2 => $posts[2], 3 => $posts[3]), array('tags' => 'Foo'))
        );
    }
    
    public function testGetTaggings()
    {
        $postManager = $this->getMockBuilder('NodePub\BlogEngine\PostManager')
            ->setMethods(array('getPostIndex'))
            ->disableOriginalConstructor()
            ->getMock();

        $postManager->expects($this->once())
            ->method('getPostIndex')
            ->will($this->returnValue($this->postIndex));

        $expected = array(
            'Foo' => 3,
            'Bar Bas' => 2,
            'Snurp' => 1,
            'Dynamo' => 1,
            'Goo' => 1,
            'Snork' => 1,
            'Larp' => 1
        );

        $this->assertEquals($expected, $postManager->getTaggings());
    }

    public function testGetTags()
    {
        $taggings = array(
            'Foo' => 4,
            'Bar Bas' => 10,
            'iPhone' => 5
        );

        $postManager = $this->getMockBuilder('NodePub\BlogEngine\PostManager')
            ->setMethods(array('getTaggings'))
            ->disableOriginalConstructor()
            ->getMock();

        $postManager->expects($this->once())
            ->method('getTaggings')
            ->will($this->returnValue($taggings));

        $expected = array(
            'foo' => array('Foo' => 4),
            'bar-bas' => array('Bar Bas' => 10),
            'iphone' => array('iPhone' => 5)
        );

        $this->assertEquals($expected, $postManager->getTags());
    }
    
    // public function testRenamePostFile()
    // {
    // }

    // public function testSavePost()
    // {
    // }
    
    // public function testDeletePost()
    // {
    // }
}
