<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::strcasecmp
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrcasecmpTest extends PHPUnit_Framework_TestCase
{
    public function test_compare_equal()
    {
        $str_x = 'iñtërnâtiônàlizætiøn';
        $str_y = 'IÑTËRNÂTIÔNÀLIZÆTIØN';
        $this->assertEquals(0, u::strcasecmp($str_x, $str_y));
    }

    public function test_less()
    {
        $str_x = 'iñtërnâtiônàlizætiøn';
        $str_y = 'IÑTËRNÂTIÔÀLIZÆTIØN';
        $this->assertTrue(u::strcasecmp($str_x, $str_y) > 0);
    }

    public function test_greater()
    {
        $str_x = 'iñtërnâtiôàlizætiøn';
        $str_y = 'IÑTËRNÂTIÔNÀLIZÆTIØN';
        $this->assertTrue(u::strcasecmp($str_x, $str_y) < 0);
    }

    public function test_empty_x()
    {
        $str_x = '';
        $str_y = 'IÑTËRNÂTIÔNÀLIZÆTIØN';
        $this->assertTrue(u::strcasecmp($str_x, $str_y) < 0);
    }

    public function test_empty_y()
    {
        $str_x = 'iñtërnâtiôàlizætiøn';
        $str_y = '';
        $this->assertTrue(u::strcasecmp($str_x, $str_y) > 0);
    }

    public function test_empty_both()
    {
        $str_x = '';
        $str_y = '';
        $this->assertTrue(u::strcasecmp($str_x, $str_y) == 0);
    }

    public function test_linefeed()
    {
        $str_x = "iñtërnâtiôn\nàlizætiøn";
        $str_y = "IÑTËRNÂTIÔN\nÀLIZÆTIØN";
        $this->assertTrue(u::strcasecmp($str_x, $str_y) == 0);
    }

}
