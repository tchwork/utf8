<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::strrev
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrrevTest extends PHPUnit_Framework_TestCase
{
    public function test_reverse()
    {
        $str = 'Iñtërnâtiônàlizætiøn';
        $rev = 'nøitæzilànôitânrëtñI';
        $this->assertEquals($rev, u::strrev($str));
    }

    public function test_empty_str()
    {
        $str = '';
        $rev = '';
        $this->assertEquals($rev, u::strrev($str));
    }

    public function test_linefeed()
    {
        $str = "Iñtërnâtiôn\nàlizætiøn";
        $rev = "nøitæzilà\nnôitânrëtñI";
        $this->assertEquals($rev, u::strrev($str));
    }
}
