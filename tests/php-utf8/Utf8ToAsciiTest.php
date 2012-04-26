<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::toAscii
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8ToAsciiTest extends PHPUnit_Framework_TestCase
{
    public function test_utf8()
    {
        $str = 'testiñg';
        $this->assertEquals('testing', u::toAscii($str));
    }

    public function test_ascii()
    {
        $str = 'testing';
        $this->assertEquals('testing', u::toAscii($str));
    }

    public function test_invalid_char()
    {
        $str = "tes\xe9ting";
        $this->assertEmpty(u::toAscii($str));
    }

    public function test_empty_str()
    {
        $str = '';
        $this->assertEmpty(u::toAscii($str));
    }

    public function test_nul_and_non_7_bit()
    {
        $str = "a\x00ñ\x00c";
        $this->assertEquals("a\x00n\x00c", u::toAscii($str));
    }

    public function test_nul()
    {
        $str = "a\x00b\x00c";
        $this->assertEquals($str, u::toAscii($str));
    }
}
