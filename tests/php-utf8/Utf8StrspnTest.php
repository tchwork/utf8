<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::strspn
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrspnTest extends PHPUnit_Framework_TestCase
{
    public function test_match()
    {
        $str = 'iñtërnâtiônàlizætiøn';
        $this->assertEquals(11, u::strspn($str, 'âëiônñrt'));
    }

    public function test_match_two()
    {
        $str = 'iñtërnâtiônàlizætiøn';
        $this->assertEquals(4, u::strspn($str, 'iñtë'));
    }

    public function test_compare_strspn()
    {
        $str = 'aeioustr';
        $this->assertEquals(strspn($str, 'saeiou'), u::strspn($str, 'saeiou'));
    }

    public function test_match_ascii()
    {
        $str = 'internationalization';
        $this->assertEquals(strspn($str, 'aeionrt'), u::strspn($str, 'aeionrt'));
    }

    public function test_linefeed()
    {
        $str = "iñtërnât\niônàlizætiøn";
        $this->assertEquals(8, u::strspn($str, 'âëiônñrt'));
    }

    public function test_linefeed_mask()
    {
        $str = "iñtërnât\niônàlizætiøn";
        $this->assertEquals(12, u::strspn($str, "âëiônñrt\n"));
    }
}
