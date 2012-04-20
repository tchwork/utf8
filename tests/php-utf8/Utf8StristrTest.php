<?php

use Patchwork\Utf8 as u;

class Utf8StristrTest extends PHPUnit_Framework_TestCase
{
    public function test_substr()
    {
        $str = 'iñtërnâtiônàlizætiøn';
        $search = 'NÂT';
        $this->assertEquals('nâtiônàlizætiøn', u::stristr($str, $search));
    }

    public function test_substr_no_match()
    {
        $str = 'iñtërnâtiônàlizætiøn';
        $search = 'foo';
        $this->assertFalse(u::stristr($str, $search));
    }

    public function test_empty_search()
    {
        $str = 'iñtërnâtiônàlizætiøn';
        $search = '';
        $this->assertEquals('iñtërnâtiônàlizætiøn', u::stristr($str, $search));
    }

    public function test_empty_str()
    {
        $str = '';
        $search = 'NÂT';
        $this->assertFalse(u::stristr($str, $search));
    }

    public function test_empty_both()
    {
        $str = '';
        $search = '';
        $this->assertEmpty(u::stristr($str, $search));
    }

    public function test_linefeed_str()
    {
        $str = "iñt\nërnâtiônàlizætiøn";
        $search = 'NÂT';
        $this->assertEquals('nâtiônàlizætiøn', u::stristr($str, $search));
    }

    public function test_linefeed_both()
    {
        $str = "iñtërn\nâtiônàlizætiøn";
        $search = "N\nÂT";
        $this->assertEquals("n\nâtiônàlizætiøn", u::stristr($str, $search));
    }
}
