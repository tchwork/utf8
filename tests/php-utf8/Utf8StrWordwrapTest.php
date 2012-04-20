<?php

use Patchwork\Utf8 as u;

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
        $wrapped = "Iñtërnâ\ntiônàliz\nætiøn";
        $this->assertEquals($wrapped, u::wordwrap($str, 10));
    }

    public function test_break_at_ten_br()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $wrapped = "Iñtërnâ<br>tiônàliz<br>ætiøn";
        $this->assertEquals($wrapped, u::wordwrap($str, 10, '<br>'));
    }

    public function test_break_at_ten_int()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $wrapped = "Iñtërnâ 우리をあöä tiônàliz 우리をあöä ætiøn";
        $this->assertEquals($wrapped, u::wordwrap($str, 10, ' 우리をあöä '));
    }
}
