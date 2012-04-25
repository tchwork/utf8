<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::substr
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8SubstrTest extends PHPUnit_Framework_TestCase
{
    public function test_utf8()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('Iñ', u::substr($str, 0, 2));
    }

    public function test_utf8_two()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('të', u::substr($str, 2, 2));
    }

    public function test_utf8_zero()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('Iñtërnâtiônàlizætiøn', u::substr($str, 0));
    }

    public function test_utf8_zero_zero()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('', u::substr($str, 0, 0));
    }

    public function test_start_great_than_length()
    {
        $str = 'Iñt';
        $this->assertEmpty(u::substr($str, 4));
    }

    public function test_compare_start_great_than_length()
    {
        $str = 'abc';
        $this->assertEquals(substr($str, 4), u::substr($str, 4));
    }

    public function test_length_beyond_string()
    {
        $str = 'Iñt';
        $this->assertEquals('ñt', u::substr($str, 1, 5));
    }

    public function test_compare_length_beyond_string()
    {
        $str = 'abc';
        $this->assertEquals(substr($str, 1, 5), u::substr($str, 1, 5));
    }

    public function test_start_negative()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('tiøn', u::substr($str, -4));
    }

    public function test_length_negative()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('nàlizæti', u::substr($str, 10, -2));
    }

    public function test_start_length_negative()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('ti', u::substr($str, -4, -2));
    }

    public function test_linefeed()
    {
        $str = "Iñ\ntërnâtiônàlizætiøn";
        $this->assertEquals("ñ\ntër", u::substr($str, 1, 5));
    }

    public function test_long_length()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('Iñtërnâtiônàlizætiøn', u::substr($str, 0, 15536));
    }
}
