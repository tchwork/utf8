<?php

namespace Patchwork\Tests\PHP\Override;

use Patchwork\PHP\Override\Intl as i;
use Normalizer as n;

class IntlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    function testGrapheme_extract_arrayWarning()
    {
        i::grapheme_extract(array(), 0);
    }

    function testGrapheme_extract()
    {
        $this->assertSame( grapheme_extract('',    0), i::grapheme_extract('',    0) );
        $this->assertSame( grapheme_extract('abc', 0), i::grapheme_extract('abc', 0) );

        $this->assertSame( i::grapheme_extract('한국어', 2, GRAPHEME_EXTR_COUNT, 3, $next), '국어' );
        $this->assertSame( $next, 9 );

        $this->assertSame(    grapheme_extract('한국어', 2, GRAPHEME_EXTR_COUNT, 3, $next), '국어' );
        $this->assertSame( $next, 9 );

        $next = 0;
        $this->assertSame( i::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next), '한' );
        $this->assertSame( i::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next), '국' );
        $this->assertSame( i::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next), '어' );
        $this->assertSame( i::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next), '' );
    }

    function testGrapheme_extract_todo()
    {
        $this->assertSame( 'a',    grapheme_extract('abc', 1, GRAPHEME_EXTR_MAXBYTES) );

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

    function testGrapheme_strlen()
    {
        $this->assertSame( i::grapheme_strlen('한국어'), 3 );
        $this->assertSame( i::grapheme_strlen(n::normalize('한국어', n::NFD)), 3 );
    }

    function testGrapheme_substr()
    {
        $c = "déjà";

        $this->assertSame( i::grapheme_substr($c,  2    ), "jà" );
        $this->assertSame( i::grapheme_substr($c, -2    ), "jà" );
        $this->assertSame( i::grapheme_substr($c, -2,  3), "jà" );
        $this->assertSame( i::grapheme_substr($c, -2, -1), "j" );
        $this->assertSame( i::grapheme_substr($c, -1,  0), "" );
        $this->assertSame( i::grapheme_substr($c, -2, -2), "" );
        $this->assertSame( i::grapheme_substr($c,  5,  0), false );
        $this->assertSame( i::grapheme_substr($c, -5,  0), false );
        $this->assertSame( i::grapheme_substr($c,  1, -4), false );
    }

    function testGrapheme_strpos()
    {
        $this->assertSame( i::grapheme_strpos('한국어', '국'), 1 );
        $this->assertSame( i::grapheme_stripos('DÉJÀ', 'à'), 3 );
        $this->assertSame( i::grapheme_strrpos('한국어', '국'), 1 );
        $this->assertSame( i::grapheme_strripos('DÉJÀ', 'à'), 3 );
    }
}
