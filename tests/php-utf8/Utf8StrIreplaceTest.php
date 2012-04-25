<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::str_ireplace
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrIreplaceTest extends PHPUnit_Framework_TestCase
{
    public function test_replace()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Iñtërnâtiônàlisetiøn';
        $this->assertEquals($replaced, u::str_ireplace('lIzÆ', 'lise', $str));
    }

    public function test_replace_no_match()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals($replaced, u::str_ireplace('foo', 'bar', $str));
    }

    public function test_empty_string()
    {
        $str = '';
        $replaced = '';
        $this->assertEquals($replaced, u::str_ireplace('foo', 'bar', $str));
    }

    public function test_empty_search()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals($replaced, u::str_ireplace('', 'x', $str));
    }

    public function test_replace_count()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'IñtërXâtiôXàlizætiøX';
        $this->assertEquals($replaced, u::str_ireplace('n', 'X', $str, $count));
        $this->assertEquals(3, $count);
    }

    public function test_replace_different_search_replace_length()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'IñtërXXXâtiôXXXàlizætiøXXX';
        $this->assertEquals($replaced, u::str_ireplace('n', 'XXX', $str));
    }

    public function test_replace_array_ascii_search()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'Iñyërxâyiôxàlizæyiøx';
        $this->assertEquals($replaced, u::str_ireplace(array('n', 't'), array('x', 'y'), $str));
    }

    public function test_replace_array_utf8_search()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'I?tërnâti??nàliz????ti???n';
        $this->assertEquals(
            u::str_ireplace(
                array('Ñ', 'ô', 'ø', 'Æ'),
                array('?', '??', '???', '????'),
                $str),
            $replaced);
    }

    public function test_replace_array_string_replace()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'I?tërnâti?nàliz?ti?n';
        $this->assertEquals(
            $replaced,
            u::str_ireplace(
                array('Ñ', 'ô', 'ø', 'Æ'),
                '?',
                $str)
        );
    }

    public function test_replace_array_single_array_replace()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $replaced = 'I?tërnâtinàliztin';
        $this->assertEquals(
            u::str_ireplace(
                array('Ñ', 'ô', 'ø', 'Æ'),
                array('?'),
                $str),
            $replaced);
    }

    public function test_replace_linefeed()
    {
        $str = "Iñtërnâti\nônàlizætiøn";
        $replaced = "Iñtërnâti\nônàlisetiøn";
        $this->assertEquals($replaced, u::str_ireplace('lIzÆ', 'lise', $str));
    }

    public function test_replace_linefeed_search()
    {
        $str = "Iñtërnâtiônàli\nzætiøn";
        $replaced = "Iñtërnâtiônàlisetiøn";
        $this->assertEquals($replaced, u::str_ireplace("lI\nzÆ", 'lise', $str));
    }
}
