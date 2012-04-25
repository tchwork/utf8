<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::strpos
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrposTest extends PHPUnit_Framework_TestCase
{
    public function test_utf8()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals(6, u::strpos($str, 'â'));
    }

    public function test_utf8_offset()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals(19, u::strpos($str, 'n', 11));
    }

    public function test_utf8_invalid()
    {
        $str = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertFalse(u::strpos($str, 'æ'));
    }

    public function test_ascii()
    {
        $str = 'ABC 123';
        $this->assertEquals(1, u::strpos($str, 'B'));
    }

    public function test_vs_strpos()
    {
        $str = 'ABC 123 ABC';
        $this->assertEquals(strpos($str, 'B', 3), u::strpos($str, 'B', 3));
    }

    public function test_empty_str()
    {
        $str = '';
        $this->assertFalse(u::strpos($str, 'x'));
    }
}
