<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::strrpos
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrrposTest extends PHPUnit_Framework_TestCase
{
    public function test_utf8()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals(17, u::strrpos($str, 'i'));
    }

    public function test_utf8_offset()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals(19, u::strrpos($str, 'n', 11));
    }

    public function test_utf8_invalid()
    {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertFalse(u::strrpos($str, 'æ'));
    }

    public function test_ascii()
    {
        $str = 'ABC ABC';
        $this->assertEquals(5, u::strrpos($str, 'B'));
    }

    public function test_vs_strpos()
    {
        $str = 'ABC 123 ABC';
        $this->assertEquals(strrpos($str, 'B'), u::strrpos($str, 'B'));
    }

    public function test_empty_str()
    {
        $str = '';
        $this->assertFalse(u::strrpos($str, 'x'));
    }

    public function test_linefeed()
    {
        $str = "Iñtërnâtiônàlizætiø\nn";
        $this->assertEquals(17, u::strrpos($str, 'i'));
    }

    public function test_linefeed_search()
    {
        $str = "Iñtërnâtiônàlizætiø\nn";
        $this->assertEquals(19, u::strrpos($str, "\n"));
    }
}
