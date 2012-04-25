<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::strcspn
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrcspnTest extends PHPUnit_Framework_TestCase
{
    public function test_no_match_single_byte_search()
    {
        $str = 'iñtërnâtiônàlizætiøn';
        $this->assertEquals(2, u::strcspn($str, 't'));
    }

    protected function tes_no_match_multi_byte_search()
    {
        $str = 'iñtërnâtiônàlizætiøn';
        $this->assertEquals(6, u::strcspn($str, 'â'));
    }

    public function test_compare_strspn()
    {
        $str = 'aeioustr';
        $this->assertEquals(strcspn($str, 'tr'), u::strcspn($str, 'tr'));
    }

    public function test_match_ascii()
    {
        $str = 'internationalization';
        $this->assertEquals(strcspn($str, 'a'), u::strcspn($str, 'a'));
    }

    public function test_linefeed()
    {
        $str = "i\nñtërnâtiônàlizætiøn";
        $this->assertEquals(3, u::strcspn($str, 't'));
    }

    public function test_linefeed_mask()
    {
        $str = "i\nñtërnâtiônàlizætiøn";
        $this->assertEquals(1, u::strcspn($str, "\n"));
    }
}
