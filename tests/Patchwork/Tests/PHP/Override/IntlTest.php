<?php

namespace Patchwork\Tests\PHP\Override;

use Patchwork\PHP\Override\Intl as i;
use Normalizer as n;

class IntlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @covers Patchwork\PHP\Override\Intl::grapheme_extract
     */
    function testGrapheme_extract_arrayWarning()
    {
        i::grapheme_extract(array(), 0);
    }

    /**
     * @covers Patchwork\PHP\Override\Intl::grapheme_extract
     */
    function testGrapheme_extract()
    {
        $this->assertSame( grapheme_extract('',    0), i::grapheme_extract('',    0) );
        $this->assertSame( grapheme_extract('abc', 0), i::grapheme_extract('abc', 0) );

        $this->assertSame( '국어', i::grapheme_extract('한국어', 2, GRAPHEME_EXTR_COUNT, 3, $next) );
        $this->assertSame( 9, $next );

        $this->assertSame( '국어', grapheme_extract('한국어', 2, GRAPHEME_EXTR_COUNT, 3, $next) );
        $this->assertSame( 9, $next );

        $next = 0;
        $this->assertSame( '한', i::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next) );
        $this->assertSame( '국', i::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next) );
        $this->assertSame( '어', i::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next) );
        $this->assertSame( '', i::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next) );
    }

    /**
     * @covers Patchwork\PHP\Override\Intl::grapheme_extract
     */
    function testGrapheme_extract_todo()
    {
        $this->assertSame( 'a', grapheme_extract('abc', 1, GRAPHEME_EXTR_MAXBYTES) );

        try
        {
            $this->assertSame( 'a', i::grapheme_extract('abc', 1, GRAPHEME_EXTR_MAXBYTES) );
            $this->assertFalse( true, "As the current implementation is incomplete, this point should not be reached currently." );
        }
        catch (\PHPUnit_Framework_Error_Warning $e)
        {
            $this->markTestIncomplete( "The current implementation doesn't handle unaligned binary offsets nor modes other than GRAPHEME_EXTR_COUNT." );
        }
    }

    /**
     * @covers Patchwork\PHP\Override\Intl::grapheme_strlen
     */
    function testGrapheme_strlen()
    {
        $this->assertSame( 3, i::grapheme_strlen('한국어') );
        $this->assertSame( 3, i::grapheme_strlen(n::normalize('한국어', n::NFD)) );

        $this->assertSame( 3, grapheme_strlen('한국어') );
        $this->assertSame( 3, grapheme_strlen(n::normalize('한국어', n::NFD)) );
    }

    /**
     * @covers Patchwork\PHP\Override\Intl::grapheme_substr
     */
    function testGrapheme_substr()
    {
        $c = "déjà";

        $this->assertSame( "jà", i::grapheme_substr($c,  2) );
        $this->assertSame( "jà", i::grapheme_substr($c, -2) );
        $this->assertSame( "jà", i::grapheme_substr($c, -2, 3) );
        $this->assertSame( "j", i::grapheme_substr($c, -2, -1) );
        $this->assertSame( "", i::grapheme_substr($c, -1,  0) );
        $this->assertSame( "", i::grapheme_substr($c, -2, -2) );
        $this->assertSame( false, i::grapheme_substr($c,  5,  0) );
        $this->assertSame( false, i::grapheme_substr($c, -5,  0) );
        $this->assertSame( false, i::grapheme_substr($c,  1, -4) );

        $this->assertSame( grapheme_substr($c,  2    ), "jà" );
        $this->assertSame( grapheme_substr($c, -2    ), "jà" );
        if (PHP_VERSION_ID >= 50400)
        {
            $this->assertSame( grapheme_substr($c, -2,  3), "jà" );
            $this->assertSame( grapheme_substr($c, -1,  0), "" );
            $this->assertSame( grapheme_substr($c,  1, -4), false );
        }
        $this->assertSame( grapheme_substr($c, -2, -1), "j" );
        $this->assertSame( grapheme_substr($c, -2, -2), "" );
        $this->assertSame( grapheme_substr($c,  5,  0), false );
        $this->assertSame( grapheme_substr($c, -5,  0), false );
    }

    /**
     * @covers Patchwork\PHP\Override\Intl::grapheme_strpos
     * @covers Patchwork\PHP\Override\Intl::grapheme_stripos
     * @covers Patchwork\PHP\Override\Intl::grapheme_strrpos
     * @covers Patchwork\PHP\Override\Intl::grapheme_strripos
     * @covers Patchwork\PHP\Override\Intl::grapheme_position
     */
    function testGrapheme_strpos()
    {
        $this->assertSame( false, i::grapheme_strpos('abc', '') );
        $this->assertSame( false, i::grapheme_strpos('abc', 'd') );
        $this->assertSame( false, i::grapheme_strpos('abc', 'a', 3) );
        $this->assertSame( 0, i::grapheme_strpos('abc', 'a', -1) );
        $this->assertSame( 1, i::grapheme_strpos('한국어', '국') );
        $this->assertSame( 3, i::grapheme_stripos('DÉJÀ', 'à') );
        $this->assertSame( 1, i::grapheme_strrpos('한국어', '국') );
        $this->assertSame( 3, i::grapheme_strripos('DÉJÀ', 'à') );

        $this->assertSame( false, grapheme_strpos('abc', '') );
        $this->assertSame( false, grapheme_strpos('abc', 'd') );
        $this->assertSame( false, grapheme_strpos('abc', 'a', 3) );
        $this->assertSame( 0, grapheme_strpos('abc', 'a', -1) );
        $this->assertSame( 1, grapheme_strpos('한국어', '국') );
        $this->assertSame( 3, grapheme_stripos('DÉJÀ', 'à') );
        $this->assertSame( 1, grapheme_strrpos('한국어', '국') );
        $this->assertSame( 3, grapheme_strripos('DÉJÀ', 'à') );
    }

    /**
     * @covers Patchwork\PHP\Override\Intl::grapheme_strstr
     * @covers Patchwork\PHP\Override\Intl::grapheme_stristr
     */
    function testGrapheme_strstr()
    {
        $this->assertSame( '국어', i::grapheme_strstr('한국어', '국') );
        $this->assertSame( 'ÉJÀ', i::grapheme_stristr('DÉJÀ', 'é') );

        $this->assertSame( '국어', grapheme_strstr('한국어', '국') );
        $this->assertSame( 'ÉJÀ', grapheme_stristr('DÉJÀ', 'é') );
    }
}
