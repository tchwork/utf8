<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::ucfirst
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8UcfirstTest extends PHPUnit_Framework_TestCase
{
    public function test_ucfirst()
    {
        $str = 'ñtërnâtiônàlizætiøn';
        $ucfirst = 'Ñtërnâtiônàlizætiøn';
        $this->assertEquals($ucfirst, u::ucfirst($str));
    }

    public function test_ucfirst_space()
    {
        $str = ' iñtërnâtiônàlizætiøn';
        $ucfirst = ' iñtërnâtiônàlizætiøn';
        $this->assertEquals($ucfirst, u::ucfirst($str));
    }

    public function test_ucfirst_upper()
    {
        $str = 'Ñtërnâtiônàlizætiøn';
        $ucfirst = 'Ñtërnâtiônàlizætiøn';
        $this->assertEquals($ucfirst, u::ucfirst($str));
    }

    public function test_empty_string()
    {
        $str = '';
        $this->assertEquals('', u::ucfirst($str));
    }

    public function test_one_char()
    {
        $str = 'ñ';
        $ucfirst = "Ñ";
        $this->assertEquals($ucfirst, u::ucfirst($str));
    }

    public function test_linefeed()
    {
        $str = "ñtërn\nâtiônàlizætiøn";
        $ucfirst = "Ñtërn\nâtiônàlizætiøn";
        $this->assertEquals($ucfirst, u::ucfirst($str));
    }
}
