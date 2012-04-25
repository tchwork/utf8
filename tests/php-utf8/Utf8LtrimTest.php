<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::ltrim
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8LtrimTest extends PHPUnit_Framework_TestCase
{
    public function test_trim()
    {
        $str = 'ñtërnâtiônàlizætiøn';
        $trimmed = 'tërnâtiônàlizætiøn';
        $this->assertEquals($trimmed, u::ltrim($str, 'ñ'));
    }

    public function test_no_trim()
    {
        $str = ' Iñtërnâtiônàlizætiøn';
        $trimmed = ' Iñtërnâtiônàlizætiøn';
        $this->assertEquals($trimmed, u::ltrim($str, 'ñ'));
    }

    public function test_empty_string()
    {
        $str = '';
        $trimmed = '';
        $this->assertEquals($trimmed, u::ltrim($str));
    }

    public function test_forward_slash()
    {
        $str = '/Iñtërnâtiônàlizætiøn';
        $trimmed = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals($trimmed, u::ltrim($str, '/'));
    }

    public function test_negate_char_class()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $trimmed = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals($trimmed, u::ltrim($str, '^s'));
    }

    public function test_linefeed()
    {
        $str = "ñ\nñtërnâtiônàlizætiøn";
        $trimmed = "\nñtërnâtiônàlizætiøn";
        $this->assertEquals($trimmed, u::ltrim($str, 'ñ'));
    }

    public function test_linefeed_mask()
    {
        $str = "ñ\nñtërnâtiônàlizætiøn";
        $trimmed = "tërnâtiônàlizætiøn";
        $this->assertEquals($trimmed, u::ltrim($str, "ñ\n"));
    }
}
