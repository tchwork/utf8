<?php

namespace Patchwork\Tests;

use Patchwork\TurkishUtf8 as u;

/**
 * @covers Patchwork\TurkishUtf8::<!public>
 */
class TurkishUtf8Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\TurkishUtf8::strtocasefold
     */
    public function testStrtocasefold()
    {
        $this->assertSame('ııii', u::strtocasefold('Iıİi'));
    }

    /**
     * @covers Patchwork\TurkishUtf8::strtolower
     * @covers Patchwork\TurkishUtf8::strtoupper
     */
    public function testStrCase()
    {
        $this->assertSame('déjà ııii', u::strtolower('DÉJÀ Iıİi'));
        $this->assertSame('DÉJÀ IIİİ', u::strtoupper('Déjà Iıİi'));
    }

    /**
     * @covers Patchwork\TurkishUtf8::strcasecmp
     * @covers Patchwork\TurkishUtf8::strnatcasecmp
     * @covers Patchwork\TurkishUtf8::strncasecmp
     * @covers Patchwork\TurkishUtf8::substr_compare
     */
    public function testStrCmp()
    {
        $this->assertSame(0, u::strcasecmp('DÉJÀ Iıİi', 'déjà ııii'));
        $this->assertSame(0, u::strnatcasecmp('DÉJÀ Iıİi', 'déjà ııii'));
        $this->assertSame(0, u::strncasecmp('Iıİiabc', 'ııiidef', 4));
        $this->assertSame(0, u::substr_compare('abcIıİidef', 'ııii', 3, 4, true));
    }

    /**
     * @covers Patchwork\TurkishUtf8::stripos
     * @covers Patchwork\TurkishUtf8::strripos
     */
    public function testStripos()
    {
        $this->assertSame(1, u::stripos('-IıİiIıİi-', 'ıIiİ'));
        $this->assertSame(5, u::strripos('-IıİiIıİi-', 'ıIiİ'));
    }

    /**
     * @covers Patchwork\TurkishUtf8::stristr
     * @covers Patchwork\TurkishUtf8::strrichr
     */
    public function testStristr()
    {
        $this->assertSame('IıİiIıİi-', u::stristr('-IıİiIıİi-', 'ıIiİ'));
        $this->assertSame('Iıİi-', u::strrichr('-IıİiIıİi-', 'ıIiİ'));
    }

    /**
     * @covers Patchwork\TurkishUtf8::str_ireplace
     */
    public function testStr_ireplace()
    {
        $this->assertSame('-IıAA-', u::str_ireplace('i', 'A', '-Iıİi-'));
        $this->assertSame('-AAİi-', u::str_ireplace('I', 'A', '-Iıİi-'));
        $this->assertSame('-', u::str_ireplace('', '', '-'));
    }

    /**
     * @covers Patchwork\TurkishUtf8::lcfirst
     * @covers Patchwork\TurkishUtf8::ucfirst
     * @covers Patchwork\TurkishUtf8::ucwords
     */
    public function testLcFirst()
    {
        $this->assertSame('iia', u::lcfirst('İia'));
        $this->assertSame('ııa', u::lcfirst('Iıa'));
        $this->assertSame('İia', u::ucfirst('iia'));
        $this->assertSame('Iıa', u::ucfirst('ııa'));
        $this->assertSame('İia Iıa', u::ucwords('iia ııa'));
    }
}
