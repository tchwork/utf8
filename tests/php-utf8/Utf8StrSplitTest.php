<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::str_split
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrSplitTest extends PHPUnit_Framework_TestCase
{
    public function test_split_one_char()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $array = array(
            'I','ñ','t','ë','r','n','â','t','i','ô','n','à','l','i',
            'z','æ','t','i','ø','n',
            );

        $this->assertEquals($array, u::str_split($str));
    }

    public function test_split_five_chars()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $array = array(
            'Iñtër','nâtiô','nàliz','ætiøn',
            );

        $this->assertEquals($array, u::str_split($str, 5));
    }

    public function test_split_six_chars()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $array = array(
            'Iñtërn','âtiônà', 'lizæti','øn',
            );

        $this->assertEquals($array, u::str_split($str, 6));
    }

    public function test_split_long()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $array = array(
            'Iñtërnâtiônàlizætiøn',
            );

        $this->assertEquals($array, u::str_split($str, 40));
    }

    public function test_split_newline()
    {
        $str = "Iñtërn\nâtiônàl\nizætiøn\n";
        $array = array(
            'I','ñ','t','ë','r','n',"\n",'â','t','i','ô','n','à','l',"\n",'i',
            'z','æ','t','i','ø','n',"\n",
            );

        $this->assertEquals($array, u::str_split($str));
    }
}
