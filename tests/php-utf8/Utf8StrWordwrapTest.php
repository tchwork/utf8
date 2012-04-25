<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::wordwrap
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrWordwrapTest extends PHPUnit_Framework_TestCase
{
    public function test_no_args()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $wrapped = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals($wrapped, u::wordwrap($str));
    }

    public function test_break_at_ten()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $wrapped = "Iñtërnâtiô\nnàlizætiøn";
        $this->assertEquals($wrapped, u::wordwrap($str, 10, "\n", true));
    }

    public function test_break_at_ten_br()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $wrapped = "Iñtërnâtiô<br>nàlizætiøn";
        $this->assertEquals($wrapped, u::wordwrap($str, 10, '<br>', true));
    }

    public function test_break_at_ten_int()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $wrapped = "Iñtërnâtiô 우리をあöä nàlizætiøn";
        $this->assertEquals($wrapped, u::wordwrap($str, 10, ' 우리をあöä ', true));
    }
}
