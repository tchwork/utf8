<?php

namespace Patchwork\Tests\Utf8;

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::isUtf8
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8IsValidTest extends \PHPUnit_Framework_TestCase
{
    public function test_valid_utf8()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $this->assertTrue(u::isUtf8($str));
    }

    public function test_valid_utf8_ascii()
    {
        $str = 'ABC 123';
        $this->assertTrue(u::isUtf8($str));
    }

    public function test_invalid_utf8()
    {
        $str = "Iñtërnâtiôn\xE9àlizætiøn";
        $this->assertFalse(u::isUtf8($str));
    }

    public function test_invalid_utf8_ascii()
    {
        $str = "this is an invalid char '\xE9' here";
        $this->assertFalse(u::isUtf8($str));
    }

    public function test_empty_string()
    {
        $str = '';
        $this->assertTrue(u::isUtf8($str));
    }

    public function test_valid_two_octet_id()
    {
        $str = "\xC3\xB1";
        $this->assertTrue(u::isUtf8($str));
    }

    public function test_invalid_two_octet_sequence()
    {
        $str = "Iñtërnâtiônàlizætiøn \xC3\x28 Iñtërnâtiônàlizætiøn";
        $this->assertFalse(u::isUtf8($str));
    }

    public function test_invalid_id_between_twoAnd_three()
    {
        $str = "Iñtërnâtiônàlizætiøn\xA0\xA1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(u::isUtf8($str));
    }

    public function test_valid_three_octet_id()
    {
        $str = "Iñtërnâtiônàlizætiøn\xE2\x82\xA1Iñtërnâtiônàlizætiøn";
        $this->assertTrue(u::isUtf8($str));
    }

    public function test_invalid_three_octet_sequence_second()
    {
        $str = "Iñtërnâtiônàlizætiøn\xE2\x28\xA1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(u::isUtf8($str));
    }

    public function test_invalid_three_octet_sequence_third()
    {
        $str = "Iñtërnâtiônàlizætiøn\xE2\x82\x28Iñtërnâtiônàlizætiøn";
        $this->assertFalse(u::isUtf8($str));
    }

    public function test_valid_four_octet_id()
    {
        $str = "Iñtërnâtiônàlizætiøn\xF0\x90\x8C\xBCIñtërnâtiônàlizætiøn";
        $this->assertTrue(u::isUtf8($str));
    }

    public function test_invalid_four_octet_sequence()
    {
        $str = "Iñtërnâtiônàlizætiøn\xF0\x28\x8C\xBCIñtërnâtiônàlizætiøn";
        $this->assertFalse(u::isUtf8($str));
    }

    public function test_invalid_five_octet_sequence()
    {
        $str = "Iñtërnâtiônàlizætiøn\xF8\xA1\xA1\xA1\xA1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(u::isUtf8($str));
    }

    public function test_invalid_six_octet_sequence()
    {
        $str = "Iñtërnâtiônàlizætiøn\xFC\xA1\xA1\xA1\xA1\xA1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(u::isUtf8($str));
    }
}
