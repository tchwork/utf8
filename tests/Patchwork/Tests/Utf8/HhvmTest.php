<?php

namespace Patchwork\Tests\Utf8;

class HhvmTest extends \PHPUnit_Framework_TestCase
{
    public function test1()
    {
        $this->assertFalse(@grapheme_extract(array(), 0));
    }

    public function test2()
    {
        // Negative offset are not allowed but native PHP silently casts them to zero
        $this->assertSame(0, grapheme_strpos('abc', 'a', -1));
    }

    public function test3()
    {
        $this->assertSame('ÉJÀ', grapheme_stristr('DÉJÀ', 'é'));
    }

    public function test4()
    {
        if (PHP_VERSION_ID >= 50400) {
            $this->assertSame('1×234¡56', number_format(1234.557, 2, '¡', '×'));
        }
    }

    public function test5()
    {
        $this->assertEquals('nàlizæti', grapheme_substr('Iñtërnâtiônàlizætiøn', 10, -2));
    }

    public function test6()
    {
        $this->assertNull(grapheme_strlen("\xE9 invalid UTF-8"));
    }

    public function test7()
    {
        $this->assertFalse(\Normalizer::normalize("\xE9 invalid UTF-8"));
    }

    public function test8()
    {
        $this->assertSame('', @(substr(array(), 0).''));
    }
}
