<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::ord
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8OrdTest extends PHPUnit_Framework_TestCase
{
    public function test_empty_str()
    {
        $str = '';
        $this->assertEquals(0, u::ord($str));
    }

    public function test_ascii_char()
    {
        $str = 'a';
        $this->assertEquals(97, u::ord($str));
    }

    public function test_2_byte_char()
    {
        $str = 'ñ';
        $this->assertEquals(241, u::ord($str));
    }

    public function test_3_byte_char()
    {
        $str = '₧';
        $this->assertEquals(8359, u::ord($str));
    }

    public function test_4_byte_char()
    {
        $str = "\xf0\x90\x8c\xbc";
        $this->assertEquals(66364, u::ord($str));
    }
}
