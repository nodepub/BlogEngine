<?php

namespace NodePub\BlogEngine\Tests;

use NodePub\BlogEngine\PermalinkFormatter;

class PermalinkFormatterTest extends \PHPUnit_Framework_TestCase
{
    protected $formatter;

    public function setUp()
    {
        $this->formatter = new PermalinkFormatter();
    }

    public function testGetPermalink()
    {
        $post = (object) array(
            'year' => 2012,
            'month' => 12,
            'slug'  => 'apocalypse'
        );

        $this->assertEquals(
            '2012/12/apocalypse',
            $this->formatter->getPermalink($post)
        );
    }
}
