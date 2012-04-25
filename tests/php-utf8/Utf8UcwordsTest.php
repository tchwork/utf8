<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::ucwords
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8UcwordsTest extends PHPUnit_Framework_TestCase
{
    public function test_ucword()
    {
        $str = 'iñtërnâtiônàlizætiøn';
        $ucwords = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals($ucwords, u::ucwords($str));
    }

    public function test_ucwords()
    {
        $str = 'iñt ërn âti ônà liz æti øn';
        $ucwords = 'Iñt Ërn Âti Ônà Liz Æti Øn';
        $this->assertEquals($ucwords, u::ucwords($str));
    }

    public function test_ucwords_newline()
    {
        $str = "iñt ërn âti\n ônà liz æti  øn";
        $ucwords = "Iñt Ërn Âti\n Ônà Liz Æti  Øn";
        $this->assertEquals($ucwords, u::ucwords($str));
    }

    public function test_empty_string()
    {
        $str = '';
        $ucwords = '';
        $this->assertEquals($ucwords, u::ucwords($str));
    }

    public function test_one_char()
    {
        $str = 'ñ';
        $ucwords = 'Ñ';
        $this->assertEquals($ucwords, u::ucwords($str));
    }

    public function test_linefeed()
    {
        $str = "iñt ërn âti\n ônà liz æti øn";
        $ucwords = "Iñt Ërn Âti\n Ônà Liz Æti Øn";
        $this->assertEquals($ucwords, u::ucwords($str));
    }
}
