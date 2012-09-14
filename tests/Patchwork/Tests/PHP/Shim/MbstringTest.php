<?php

namespace Patchwork\Tests\PHP\Shim;

use Patchwork\PHP\Shim\Mbstring as p;
use Normalizer as n;

/**
 * @covers Patchwork\PHP\Shim\Mbstring::<!public>
 */
class MbstringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strtolower
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strtoupper
     * @covers Patchwork\PHP\Shim\Mbstring::mb_convert_case
     */
    function testStrCase()
    {
        $this->assertSame( 'déjà σσς', p::mb_strtolower('DÉJÀ Σσς') );
        $this->assertSame( 'DÉJÀ ΣΣΣ', p::mb_strtoupper('Déjà Σσς') );
        $this->assertSame( 'Déjà Σσσ', p::mb_convert_case('DÉJÀ ΣΣΣ', MB_CASE_TITLE) );
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strlen
     */
    function testmb_strlen()
    {
        $this->assertSame( 3, mb_strlen('한국어') );
        $this->assertSame( 8, mb_strlen(n::normalize('한국어', n::NFD)) );

        $this->assertSame( 3, p::mb_strlen('한국어') );
        $this->assertSame( 8, p::mb_strlen(n::normalize('한국어', n::NFD)) );
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_substr
     */
    function testmb_substr()
    {
        $c = "déjà";

        $this->assertSame( "jà", mb_substr($c,  2) );
        $this->assertSame( "jà", mb_substr($c, -2) );
        $this->assertSame( "jà", mb_substr($c, -2,  3) );
        $this->assertSame( "", mb_substr($c, -1,  0) );
        $this->assertSame( "", mb_substr($c,  1, -4) );
        $this->assertSame( "j", mb_substr($c, -2, -1) );
        $this->assertSame( "", mb_substr($c, -2, -2) );
        $this->assertSame( "", mb_substr($c,  5,  0) );
        $this->assertSame( "", mb_substr($c, -5,  0) );

        $this->assertSame( "jà", p::mb_substr($c,  2) );
        $this->assertSame( "jà", p::mb_substr($c, -2) );
        $this->assertSame( "jà", p::mb_substr($c, -2, 3) );
        $this->assertSame( "", p::mb_substr($c, -1,  0) );
        $this->assertSame( "", p::mb_substr($c,  1, -4) );
        $this->assertSame( "j", p::mb_substr($c, -2, -1) );
        $this->assertSame( "", p::mb_substr($c, -2, -2) );
        $this->assertSame( "", p::mb_substr($c,  5,  0) );
        $this->assertSame( "", p::mb_substr($c, -5,  0) );
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strpos
     * @covers Patchwork\PHP\Shim\Mbstring::mb_stripos
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strrpos
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strripos
     */
    function testmb_strpos()
    {
        $this->assertSame( false, @mb_strpos('abc', '') );
        $this->assertSame( false, @mb_strpos('abc', 'a', -1) );
        $this->assertSame( false, mb_strpos('abc', 'd') );
        $this->assertSame( false, mb_strpos('abc', 'a', 3) );
        $this->assertSame( 1, mb_strpos('한국어', '국') );
        $this->assertSame( 3, mb_stripos('DÉJÀ', 'à') );
        $this->assertSame( false, mb_strrpos('한국어', '') );
        $this->assertSame( 1, mb_strrpos('한국어', '국') );
        $this->assertSame( 3, mb_strripos('DÉJÀ', 'à') );
        $this->assertSame( 1, mb_stripos('aςσb', 'ΣΣ') );
        $this->assertSame( 1, mb_strripos('aςσb', 'ΣΣ') );

        $this->assertSame( false, @p::mb_strpos('abc', '') );
        $this->assertSame( false, @p::mb_strpos('abc', 'a', -1) );
        $this->assertSame( false, p::mb_strpos('abc', 'd') );
        $this->assertSame( false, p::mb_strpos('abc', 'a', 3) );
        $this->assertSame( 1, p::mb_strpos('한국어', '국') );
        $this->assertSame( 3, p::mb_stripos('DÉJÀ', 'à') );
        $this->assertSame( false, p::mb_strrpos('한국어', '') );
        $this->assertSame( 1, p::mb_strrpos('한국어', '국') );
        $this->assertSame( 3, p::mb_strripos('DÉJÀ', 'à') );
        $this->assertSame( 1, p::mb_stripos('aςσb', 'ΣΣ') );
        $this->assertSame( 1, p::mb_strripos('aςσb', 'ΣΣ') );
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strpos
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    function testmb_strpos_empty_delimiter()
    {
        try
        {
            mb_strpos('abc', '');
            $this->assertFalse( true, "The previous line should trigger a warning (Empty delimiter)" );
        }
        catch (\PHPUnit_Framework_Error_Warning $e)
        {
            p::mb_strpos('abc', '');
            $this->assertFalse( true, "The previous line should trigger a warning (Empty delimiter)" );
        }
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strpos
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    function testmb_strpos_negative_offset()
    {
        try
        {
            mb_strpos('abc', 'a', -1);
            $this->assertFalse( true, "The previous line should trigger a warning (Offset not contained in string)" );
        }
        catch (\PHPUnit_Framework_Error_Warning $e)
        {
            p::mb_strpos('abc', 'a', -1);
            $this->assertFalse( true, "The previous line should trigger a warning (Offset not contained in string)" );
        }
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strstr
     * @covers Patchwork\PHP\Shim\Mbstring::mb_stristr
     */
    function testmb_strstr()
    {
        $this->assertSame( '국어', mb_strstr('한국어', '국') );
        $this->assertSame( 'ÉJÀ', mb_stristr('DÉJÀ', 'é') );

        $this->assertSame( '국어', p::mb_strstr('한국어', '국') );
        $this->assertSame( 'ÉJÀ', p::mb_stristr('DÉJÀ', 'é') );
    }
}
