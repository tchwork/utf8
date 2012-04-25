<?php

use Patchwork\Utf8 as u;

/**
 * @covers Patchwork\Utf8::str_pad
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8StrPadTest extends PHPUnit_Framework_TestCase
{
    public function test_str_pad()
    {
        $toPad = '<IñtërnëT>'; // 10 characters
        $padding = 'ø__'; // 4 characters

        $this->assertEquals($toPad.'          ', u::str_pad($toPad, 20));
        $this->assertEquals('          '.$toPad, u::str_pad($toPad, 20, ' ', STR_PAD_LEFT));
        $this->assertEquals('     '.$toPad.'     ', u::str_pad($toPad, 20, ' ', STR_PAD_BOTH));

        $this->assertEquals($toPad, u::str_pad($toPad, 10));
        $this->assertEquals('5char', str_pad('5char', 4)); // str_pos won't truncate input string
        $this->assertEquals($toPad, u::str_pad($toPad, 8));

        $this->assertEquals($toPad.'ø__ø__ø__ø', u::str_pad($toPad, 20, $padding, STR_PAD_RIGHT));
        $this->assertEquals('ø__ø__ø__ø'.$toPad, u::str_pad($toPad, 20, $padding, STR_PAD_LEFT));
        $this->assertEquals('ø__ø_'.$toPad.'ø__ø_', u::str_pad($toPad, 20, $padding, STR_PAD_BOTH));
    }
}
