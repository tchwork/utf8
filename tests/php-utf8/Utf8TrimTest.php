<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::trim
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8TrimTest extends PHPUnit_Framework_TestCase
{
    public function test_trim()
    {
        $str = 'ñtërnâtiônàlizætiø';
        $trimmed = 'tërnâtiônàlizæti';
        $this->assertEquals($trimmed, u::trim($str, 'ñø'));
    }

    public function test_no_trim()
    {
        $str = ' Iñtërnâtiônàlizætiøn ';
        $trimmed = ' Iñtërnâtiônàlizætiøn ';
        $this->assertEquals($trimmed, u::trim($str, 'ñø'));
    }

    public function test_empty_string()
    {
        $str = '';
        $trimmed = '';
        $this->assertEquals($trimmed, u::trim($str));
    }
}
