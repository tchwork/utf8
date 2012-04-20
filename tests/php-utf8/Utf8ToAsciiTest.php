<?php

use Patchwork\Utf8 as u;

class Utf8ToAsciiTest extends PHPUnit_Framework_TestCase
{
    public function test_utf8()
    {
        $str = 'testiñg';
        $this->assertEquals('testig', u::toASCII($str));
    }

    public function test_ascii()
    {
        $str = 'testing';
        $this->assertEquals('testing', u::toASCII($str));
    }

    public function test_invalid_char()
    {
        $str = "tes\xe9ting";
        $this->assertEquals('testing', u::toASCII($str));
    }

    public function test_empty_str()
    {
        $str = '';
        $this->assertEmpty(u::toASCII($str));
    }

    public function test_nul_and_non_7_bit()
    {
        $str = "a\x00ñ\x00c";
        $this->assertEquals('ac', u::toASCII($str, 'both'));
    }

    public function test_nul()
    {
        $str = "a\x00b\x00c";
        $this->assertEquals('abc', u::toASCII($str, 'ctrl_chars'));
    }
}
