<?php

namespace NodePub\BlogEngine\Tests;

use NodePub\BlogEngine\FilenameFormatter;
use NodePub\BlogEngine\Post;

class FilenameFormatterTest extends \PHPUnit_Framework_TestCase
{
    protected $formatter;

    public function setUp()
    {
        $this->formatter = new FilenameFormatter(FIXTURES_DIR, 'txt');
    }

    // public function testGetFilePath()
    // {
    //     $post = (object) array(
    //         'year' => 2012,
    //         'month' => 12,
    //         'day'   => 21,
    //         'slug'  => 'apocalypse'
    //     );

    //     $post = new Post($post);

    //     $this->assertEquals(
    //         FIXTURES_DIR.'/2012/2012-12-21-apocalypse.txt',
    //         $this->formatter->getFilePath($post)
    //     );
    // }

    public function testGetPostPropertiesFromFilename()
    {
        $filepath = FIXTURES_DIR.'/2012/2012-12-21-apocalypse.txt';

        $expected = array(
            'year' => 2012,
            'month' => 12,
            'day'   => 21,
            'slug'  => 'apocalypse'
        );

        $this->assertEquals(
            $expected,
            $this->formatter->getPostPropertiesFromFilename($filepath)
        );
    }
}
