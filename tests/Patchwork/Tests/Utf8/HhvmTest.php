<?php

namespace Patchwork\Tests\Utf8;

class HhvmTest extends \PHPUnit_Framework_TestCase
{
    function test1()
    {
        $this->assertFalse( @grapheme_extract(array(), 0) );
    }

    function test2()
    {
        // Negative offset are not allowed but native PHP silently casts them to zero
        $this->assertSame( 0, grapheme_strpos('abc', 'a', -1) );
    }

    function test3()
    {
        $this->assertSame( 'ÉJÀ', grapheme_stristr('DÉJÀ', 'é') );
    }

    function test4()
    {
        if (PHP_VERSION_ID >= 50400) {
            $this->assertSame( '1×234¡56', number_format(1234.557, 2, '¡', '×') );
        }
    }

    function test5()
    {
        $this->assertEquals( 'nàlizæti', grapheme_substr('Iñtërnâtiônàlizætiøn', 10, -2) );
    }

    function test6()
    {
        $this->assertNull( grapheme_strlen("\xE9 invalid UTF-8") );
    }

    function test7()
    {
        $this->assertFalse( \Normalizer::normalize("\xE9 invalid UTF-8") );
    }

    function test8()
    {
        $this->assertSame( '', @(substr(array(), 0).'') );
    }

    function test9()
    {
        $this->dummy9(123);
    }

    function dummy9($arg)
    {
        $arg = 456;

        if (defined('HHVM_VERSION')) {
            $this->assertSame( 456, func_get_arg(0) );
        } else {
            $this->assertSame( 123, func_get_arg(0) );
        }
    }
}
