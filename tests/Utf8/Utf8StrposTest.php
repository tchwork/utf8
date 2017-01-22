<?php

namespace Patchwork\Tests\Utf8;

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::strpos
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrposTest extends \PHPUnit_Framework_TestCase
{
    public function test_utf8()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals(6, u::strpos($str, 'â'));
        $this->assertEquals(6, u::stripos($str, 'Â'));
    }

    public function test_utf8_offset()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals(19, u::strpos($str, 'n', 11));
        $this->assertEquals(19, u::stripos($str, 'N', 11));
    }

    public function test_utf8_invalid()
    {
        $str = "Iñtërnâtiôn\xE9àlizætiøn";
        $this->assertFalse(u::strpos($str, 'æ'));
        $this->assertFalse(u::stripos($str, 'æ'));
    }

    public function test_ascii()
    {
        $str = 'ABC 123';
        $this->assertEquals(1, u::strpos($str, 'B'));
        $this->assertEquals(1, u::stripos($str, 'b'));
    }

    public function test_vs_strpos()
    {
        $str = 'ABC 123 ABC';
        $this->assertEquals(strpos($str, 'B', 3), u::strpos($str, 'B', 3));
        $this->assertEquals(stripos($str, 'b', 3), u::stripos($str, 'b', 3));
    }

    public function test_empty_str()
    {
        $str = '';
        $this->assertFalse(u::strpos($str, 'x'));
        $this->assertFalse(u::stripos($str, 'x'));
    }
}
