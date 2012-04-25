<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::rtrim
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8RtrimTest extends PHPUnit_Framework_TestCase
{
    public function test_trim()
    {
        $str = 'Iñtërnâtiônàlizætiø';
        $trimmed = 'Iñtërnâtiônàlizæti';
        $this->assertEquals($trimmed, u::rtrim($str, 'ø'));
    }

    public function test_no_trim()
    {
        $str = 'Iñtërnâtiônàlizætiøn ';
        $trimmed = 'Iñtërnâtiônàlizætiøn ';
        $this->assertEquals($trimmed, u::rtrim($str, 'ø'));
    }

    public function test_empty_string()
    {
        $str = '';
        $trimmed = '';
        $this->assertEquals($trimmed, u::rtrim($str));
    }

    public function test_linefeed()
    {
        $str = "Iñtërnâtiônàlizætiø\nø";
        $trimmed = "Iñtërnâtiônàlizætiø\n";
        $this->assertEquals($trimmed, u::rtrim($str, 'ø'));
    }

    public function test_linefeed_mask()
    {
        $str = "Iñtërnâtiônàlizætiø\nø";
        $trimmed = "Iñtërnâtiônàlizæti";
        $this->assertEquals($trimmed, u::rtrim($str, "ø\n"));
    }
}
