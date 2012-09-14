<?php

namespace Patchwork\Tests\PHP\Shim;

use Patchwork\PHP\Shim\Intl as p;
use Normalizer as n;

/**
 * @covers Patchwork\PHP\Shim\Intl::<!public>
 */
class IntlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @covers Patchwork\PHP\Shim\Intl::grapheme_extract
     */
    function testGrapheme_extract_arrayWarning()
    {
        p::grapheme_extract(array(), 0);
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_extract
     */
    function testGrapheme_extract()
    {
        $this->assertFalse( @grapheme_extract(array(), 0) );
        $this->assertFalse( @p::grapheme_extract(array(), 0) );

        $this->assertSame( grapheme_extract('',    0), p::grapheme_extract('',    0) );
        $this->assertSame( grapheme_extract('abc', 0), p::grapheme_extract('abc', 0) );

        $this->assertSame( '국어', p::grapheme_extract('한국어', 2, GRAPHEME_EXTR_COUNT, 3, $next) );
        $this->assertSame( 9, $next );

        $this->assertSame( '국어', grapheme_extract('한국어', 2, GRAPHEME_EXTR_COUNT, 3, $next) );
        $this->assertSame( 9, $next );

        $next = 0;
        $this->assertSame( '한', p::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next) );
        $this->assertSame( '국', p::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next) );
        $this->assertSame( '어', p::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next) );
        $this->assertSame( '', p::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next) );
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_extract
     */
    function testGrapheme_extract_todo()
    {
        $this->assertSame( 'a', grapheme_extract('abc', 1, GRAPHEME_EXTR_MAXBYTES) );

        try
        {
            $this->assertSame( 'a', p::grapheme_extract('abc', 1, GRAPHEME_EXTR_MAXBYTES) );
            $this->assertFalse( true, "As the current implementation is incomplete, this point should not be reached currently." );
        }
        catch (\PHPUnit_Framework_Error_Warning $e)
        {
            $this->markTestIncomplete( "The current implementation doesn't handle unaligned binary offsets nor modes other than GRAPHEME_EXTR_COUNT." );
        }
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_strlen
     */
    function testGrapheme_strlen()
    {
        $this->assertSame( 3, grapheme_strlen('한국어') );
        $this->assertSame( 3, grapheme_strlen(n::normalize('한국어', n::NFD)) );

        $this->assertSame( 3, p::grapheme_strlen('한국어') );
        $this->assertSame( 3, p::grapheme_strlen(n::normalize('한국어', n::NFD)) );
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_substr
     * @covers Patchwork\PHP\Shim\Intl::grapheme_substr_workaround62759
     */
    function testGrapheme_substr()
    {
        $c = "déjà";

        $this->assertSame( "jà", grapheme_substr($c,  2) );
        $this->assertSame( "jà", grapheme_substr($c, -2) );
        // The next 3 tests are disabled due to http://bugs.php.net/62759 and 55562
        //$this->assertSame( "jà", grapheme_substr($c, -2,  3) );
        //$this->assertSame( "", grapheme_substr($c, -1,  0) );
        //$this->assertSame( false, grapheme_substr($c,  1, -4) );
        $this->assertSame( "j", grapheme_substr($c, -2, -1) );
        $this->assertSame( "", grapheme_substr($c, -2, -2) );
        $this->assertSame( false, grapheme_substr($c,  5,  0) );
        $this->assertSame( false, grapheme_substr($c, -5,  0) );

        $this->assertSame( "jà", p::grapheme_substr($c,  2) );
        $this->assertSame( "jà", p::grapheme_substr($c, -2) );
        $this->assertSame( "jà", p::grapheme_substr($c, -2, 3) );
        $this->assertSame( "", p::grapheme_substr($c, -1,  0) );
        $this->assertSame( false, p::grapheme_substr($c,  1, -4) );
        $this->assertSame( "j", p::grapheme_substr($c, -2, -1) );
        $this->assertSame( "", p::grapheme_substr($c, -2, -2) );
        $this->assertSame( false, p::grapheme_substr($c,  5,  0) );
        $this->assertSame( false, p::grapheme_substr($c, -5,  0) );

        $this->assertSame( "jà", p::grapheme_substr_workaround62759($c,  2, 2147483647) );
        $this->assertSame( "jà", p::grapheme_substr_workaround62759($c, -2, 2147483647) );
        $this->assertSame( "jà", p::grapheme_substr_workaround62759($c, -2, 3) );
        $this->assertSame( "", p::grapheme_substr_workaround62759($c, -1,  0) );
        $this->assertSame( false, p::grapheme_substr_workaround62759($c,  1, -4) );
        $this->assertSame( "j", p::grapheme_substr_workaround62759($c, -2, -1) );
        $this->assertSame( "", p::grapheme_substr_workaround62759($c, -2, -2) );
        $this->assertSame( false, p::grapheme_substr_workaround62759($c,  5,  0) );
        $this->assertSame( false, p::grapheme_substr_workaround62759($c, -5,  0) );
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_strpos
     * @covers Patchwork\PHP\Shim\Intl::grapheme_stripos
     * @covers Patchwork\PHP\Shim\Intl::grapheme_strrpos
     * @covers Patchwork\PHP\Shim\Intl::grapheme_strripos
     * @covers Patchwork\PHP\Shim\Intl::grapheme_position
     */
    function testGrapheme_strpos()
    {
        $this->assertSame( false, grapheme_strpos('abc', '') );
        $this->assertSame( false, grapheme_strpos('abc', 'd') );
        $this->assertSame( false, grapheme_strpos('abc', 'a', 3) );
        $this->assertSame( 0, grapheme_strpos('abc', 'a', -1) );
        $this->assertSame( 1, grapheme_strpos('한국어', '국') );
        $this->assertSame( 3, grapheme_stripos('DÉJÀ', 'à') );
        $this->assertSame( false, grapheme_strrpos('한국어', '') );
        $this->assertSame( 1, grapheme_strrpos('한국어', '국') );
        $this->assertSame( 3, grapheme_strripos('DÉJÀ', 'à') );

        $this->assertSame( false, p::grapheme_strpos('abc', '') );
        $this->assertSame( false, p::grapheme_strpos('abc', 'd') );
        $this->assertSame( false, p::grapheme_strpos('abc', 'a', 3) );
        $this->assertSame( 0, p::grapheme_strpos('abc', 'a', -1) );
        $this->assertSame( 1, p::grapheme_strpos('한국어', '국') );
        $this->assertSame( 3, p::grapheme_stripos('DÉJÀ', 'à') );
        $this->assertSame( false, p::grapheme_strrpos('한국어', '') );
        $this->assertSame( 1, p::grapheme_strrpos('한국어', '국') );
        $this->assertSame( 3, p::grapheme_strripos('DÉJÀ', 'à') );
        $this->assertSame( 16, p::grapheme_stripos('der Straße nach Paris', 'Paris') );
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_strstr
     * @covers Patchwork\PHP\Shim\Intl::grapheme_stristr
     */
    function testGrapheme_strstr()
    {
        $this->assertSame( '국어', grapheme_strstr('한국어', '국') );
        $this->assertSame( 'ÉJÀ', grapheme_stristr('DÉJÀ', 'é') );

        $this->assertSame( '국어', p::grapheme_strstr('한국어', '국') );
        $this->assertSame( 'ÉJÀ', p::grapheme_stristr('DÉJÀ', 'é') );
        $this->assertSame( 'Paris', p::grapheme_stristr('der Straße nach Paris', 'Paris') );
    }

    function testGrapheme_bugs()
    {
        $this->assertSame( 17, grapheme_stripos('der Straße nach Paris', 'Paris') ); // Expected fail: the non-bugged result is 16
        $this->assertSame( 'aris', grapheme_stristr('der Straße nach Paris', 'Paris') );  // Expected fail: the non-bugged result is Paris
    }
}
