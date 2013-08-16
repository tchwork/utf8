<?php

namespace Patchwork\Tests;

use Patchwork\TurkishUtf8 as u;
use Normalizer as n;

/**
 * @covers Patchwork\TurkishUtf8::<!public>
 * @todo strcasecmp strnatcasecmp strccasecmp substr_compare
 */
class TurkishUtf8Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\TurkishUtf8::strtocasefold
     */
    function testStrtocasefold()
    {
        $this->assertSame( 'ııii', u::strtocasefold('Iıİi') );
    }

    /**
     * @covers Patchwork\TurkishUtf8::strtolower
     * @covers Patchwork\TurkishUtf8::strtoupper
     */
    function testStrCase()
    {
        $this->assertSame( 'déjà ııii', u::strtolower('DÉJÀ Iıİi') );
        $this->assertSame( 'DÉJÀ IIİİ', u::strtoupper('Déjà Iıİi') );
    }

    /**
     * @covers Patchwork\TurkishUtf8::stripos
     * @covers Patchwork\TurkishUtf8::strripos
     */
    function testStripos()
    {
        $this->assertSame( 1, u::stripos('-IıİiIıİi-', 'ıIiİ') );
        $this->assertSame( 5, u::strripos('-IıİiIıİi-', 'ıIiİ') );
    }

    /**
     * @covers Patchwork\TurkishUtf8::stristr
     * @covers Patchwork\TurkishUtf8::strrichr
     */
    function testStristr()
    {
        $this->assertSame( 'IıİiIıİi-', u::stristr('-IıİiIıİi-', 'ıIiİ') );
        $this->assertSame( 'Iıİi-', u::strrichr('-IıİiIıİi-', 'ıIiİ') );
    }

    /**
     * @covers Patchwork\TurkishUtf8::str_ireplace
     */
    function testStr_ireplace()
    {
        $this->assertSame( '-IıAA-', u::str_ireplace('i', 'A', '-Iıİi-') );
        $this->assertSame( '-AAİi-', u::str_ireplace('I', 'A', '-Iıİi-') );
        $this->assertSame( '-', u::str_ireplace('', '', '-') );
    }

    /**
     * @covers Patchwork\TurkishUtf8::lcfirst
     * @covers Patchwork\TurkishUtf8::ucfirst
     * @covers Patchwork\TurkishUtf8::ucwords
     */
    function testLcFirst()
    {
        $this->assertSame( 'ia', u::lcfirst('İa') );
        $this->assertSame( 'ıa', u::lcfirst('Ia') );
        $this->assertSame( 'İa', u::ucfirst('ia') );
        $this->assertSame( 'Ia', u::ucfirst('ıa') );
        $this->assertSame( 'İa Ia', u::ucwords('ia ıa') );
    }
}
