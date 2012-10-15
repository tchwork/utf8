<?php

namespace Patchwork\Tests;

use Patchwork\Utf8 as u;
use Normalizer as n;

/**
 * @covers Patchwork\Utf8::<!public>
 */
class Utf8Test extends \PHPUnit_Framework_TestCase
{
    static

    $utf8ValidityMap = array(
        "a" => true,
        "\xC3\xB1" => true,
        "\xC3\x28" => false,
        "\xA0\xA1" => false,
        "\xE2\x82\xA1" => true,
        "\xE2\x28\xA1" => false,
        "\xE2\x82\x28" => false,
        "\xF0\x90\x8C\xBC" => true,
        "\xF0\x28\x8C\xBC" => false,
        "\xF0\x90\x28\xBC" => false,
        "\xF0\x28\x8C\x28" => false,
        "\xF8\xA1\xA1\xA1\xA1" => false,
        "\xFC\xA1\xA1\xA1\xA1\xA1" => false,
    );


    /**
     * @covers Patchwork\Utf8::isUtf8
     */
    function testIsUtf8()
    {
        foreach (self::$utf8ValidityMap as $u => $t)
        {
            if ($t) $this->assertTrue( u::isUtf8($u) );
            else $this->assertFalse( u::isUtf8($u) );
        }
    }

    /**
     * @covers Patchwork\Utf8::toAscii
     */
    function testToASCII()
    {
        $this->assertSame( '', u::toAscii('') );
        $this->assertSame( 'deja vu', u::toAscii('déjà vu') );
    }

    /**
     * @covers Patchwork\Utf8::strtocasefold
     */
    function testStrtocasefold()
    {
        $this->assertSame( 'σσσ', u::strtocasefold('Σσς') );
        $this->assertSame( 'ııii', u::strtocasefold('Iıİi', true, true) ); // Turkish
    }

    /**
     * @covers Patchwork\Utf8::strtonatfold
     */
    function testStrtonatfold()
    {
        $this->assertSame( 'Deja Σσς', u::strtonatfold('Déjà Σσς') );
    }

    /**
     * @covers Patchwork\Utf8::strtolower
     * @covers Patchwork\Utf8::strtoupper
     */
    function testStrCase()
    {
        $this->assertSame( 'déjà σσς', u::strtolower('DÉJÀ Σσς') );
        $this->assertSame( 'DÉJÀ ΣΣΣ', u::strtoupper('Déjà Σσς') );
    }

    /**
     * @covers Patchwork\Utf8::substr
     */
    function testSubstr()
    {
        $b = "deja";
        $c = "déjà";
        $d = n::normalize($c, n::NFD);
        $this->assertTrue( $c > $d );

        $this->assertSame( '국어', u::substr('한국어', 1, 20) );

        $this->assertSame( "de", substr($b,  0,  2) );
        $this->assertSame( "ja", substr($b, -2,  3) );
        $this->assertSame( "ej", substr($b, -3, -1) );
        $this->assertSame( "", substr($b,  1, -3) );
        $this->assertSame( "", substr($c,  5,  0) ); // u::substr() returns false here
        $this->assertSame( "", substr($c, -5,  0) ); // u::substr() returns false here
        $this->assertSame( false, substr($b,  1, -4) );

        $this->assertSame( "jà", u::substr($c,  2    ) );
        $this->assertSame( "jà", u::substr($c, -2    ) );
        $this->assertSame( "dé", u::substr($c,  0,  2) );
        $this->assertSame( "jà", u::substr($c, -2,  3) );
        $this->assertSame( "éj", u::substr($c, -3, -1) );
        $this->assertSame( "", u::substr($c,  1, -3) );
        $this->assertSame( false, u::substr($c,  5,  0) ); // Modelled after grapheme_substr(), not substr() (see above)
        $this->assertSame( false, u::substr($c, -5,  0) ); // Modelled after grapheme_substr(), not substr() (see above)
        $this->assertSame( false, u::substr($c,  1, -4) );

        $this->assertSame( n::normalize("dé", n::NFD), u::substr($d,  0,  2) );
        $this->assertSame( n::normalize("jà", n::NFD), u::substr($d, -2,  3) );
        $this->assertSame( n::normalize("éj", n::NFD), u::substr($d, -3, -1) );
        $this->assertSame( "", u::substr($d,  1, -3) );
        $this->assertSame( false, u::substr($d,  1, -4) );
    }

    /**
     * @covers Patchwork\Utf8::strlen
     */
    function testStrlen()
    {
        foreach (self::$utf8ValidityMap as $u => $t) if ($t)
        {
            $this->assertSame( 1, u::strlen($u) );
        }

        $c = "déjà";
        $d = n::normalize($c, n::NFD);
        $this->assertTrue( $c > $d );

        $this->assertSame( 4, u::strlen($c) );
        $this->assertSame( 4, u::strlen($d) );

        $this->assertSame( 3, u::strlen(n::normalize('한국어', n::NFD)) );
    }

    /**
     * @covers Patchwork\Utf8::strpos
     * @covers Patchwork\Utf8::stripos
     * @covers Patchwork\Utf8::strrpos
     * @covers Patchwork\Utf8::strripos
     */
    function testStrpos()
    {
        $this->assertSame( false, u::strpos('abc', '') );
        $this->assertSame( false, u::strpos('abc', 'd') );
        $this->assertSame( false, u::strpos('abc', 'a', 3) );
        $this->assertSame( 0, u::strpos('abc', 'a', -1) );
        $this->assertSame( 1, u::strpos('한국어', '국') );
        $this->assertSame( 3, u::stripos('DÉJÀ', 'à') );
        $this->assertSame( 1, u::stripos('aςσb', 'ΣΣ') );
        $this->assertSame( false, u::strrpos('한국어', '') );
        $this->assertSame( 1, u::strrpos('한국어', '국') );
        $this->assertSame( 3, u::strripos('DÉJÀ', 'à') );
        $this->assertSame( 1, u::strripos('aςσb', 'ΣΣ') );
        $this->assertSame( 16, u::stripos('der Straße nach Paris', 'Paris') );
    }

    /**
     * @covers Patchwork\Utf8::strstr
     * @covers Patchwork\Utf8::stristr
     * @covers Patchwork\Utf8::strrchr
     * @covers Patchwork\Utf8::strrichr
     */
    function testStrstr()
    {
        $this->assertSame( 'éjàdéjà', u::strstr('déjàdéjà', 'é') );
        $this->assertSame( 'ÉJÀDÉJÀ', u::stristr('DÉJÀDÉJÀ', 'é') );
        $this->assertSame( 'ςσb', u::stristr('aςσb', 'ΣΣ') );
        $this->assertSame( 'éjà', u::strrchr('déjàdéjà', 'é') );
        $this->assertSame( 'ÉJÀ', u::strrichr('DÉJÀDÉJÀ', 'é') );

        $this->assertSame( 'd', u::strstr('déjàdéjà', 'é', true) );
        $this->assertSame( 'D', u::stristr('DÉJÀDÉJÀ', 'é', true) );
        $this->assertSame( 'a', u::stristr('aςσb', 'ΣΣ', true) );
        $this->assertSame( 'déjàd', u::strrchr('déjàdéjà', 'é', true) );
        $this->assertSame( 'DÉJÀD', u::strrichr('DÉJÀDÉJÀ', 'é', true) );
        $this->assertSame( 'Paris', u::stristr('der Straße nach Paris', 'Paris') );
    }

    /**
     * @covers Patchwork\Utf8::wordwrap
     */
    function testWordwrap()
    {
        $this->assertSame(
"L’École supérieure de
physique et de chimie
industrielles de la ville
de Paris, ou ESPCI
ParisTech, est une grande
école d’ingénieurs fondée
en 1882. Elle est située
rue Vauquelin sur la
montagne Sainte-Geneviève
dans le cinquième
arrondissement de Paris.
Yoooooooooooooooooooooooo
oooooooooooooooooooooo",
            u::wordwrap("L’École supérieure de physique et de chimie industrielles de la ville de Paris, ou ESPCI ParisTech, est une grande école d’ingénieurs fondée en 1882. Elle est située rue Vauquelin sur la montagne Sainte-Geneviève dans le cinquième arrondissement de Paris. Yoooooooooooooooooooooooooooooooooooooooooooooo", 25, "\n", true)
        );
    }

    /**
     * @covers Patchwork\Utf8::count_chars
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    function testCountChars()
    {
        $c = "déjà 한국어";
        $c .= n::normalize($c, n::NFD);

        $e = array(
            'd' => 2,
            'é' => 1,
            'j' => 2,
            'à' => 1,
            ' ' => 2,
            '한' => 1,
            '국' => 1,
            '어' => 1,
            'é' => 1,
            'à' => 1,
            '한' => 1,
            '국' => 1,
            '어' => 1,
        );

        $this->assertSame( $e, u::count_chars($c, 1) );
        $this->assertSame( $e, u::count_chars($c) );
        $this->assertFalse( true, 'The previous line should trigger a warning (the only allowed $mode is 1)' );
    }

    /**
     * @covers Patchwork\Utf8::chr
     * @covers Patchwork\Utf8::ord
     */
    function testChrOrd()
    {
        foreach (self::$utf8ValidityMap as $u => $t) if ($t)
        {
            $this->assertSame( $u, u::chr(u::ord($u)) );
        }
    }

    /**
     * @covers Patchwork\Utf8::str_pad
     */
    function testStr_pad()
    {
        $this->assertSame( 'ÉÈà-à-à-à-', u::str_pad('ÉÈ', 10, 'à-', STR_PAD_RIGHT) );
        $this->assertSame( 'à-à-à-à-ÉÈ', u::str_pad('ÉÈ', 10, 'à-', STR_PAD_LEFT ) );
        $this->assertSame( 'à-à-ÉÈà-à-', u::str_pad('ÉÈ', 10, 'à-', STR_PAD_BOTH ) );
    }

    /**
     * @covers Patchwork\Utf8::str_shuffle
     */
    function testStr_shuffle()
    {
        $c = "déjà 한국어";
        $c .= n::normalize($c, n::NFD);

        $this->assertTrue(
               $c != ($d = u::str_shuffle($c))
            || $c != ($d = u::str_shuffle($c))
        );

        $this->assertSame( strlen($c), strlen($d) );
        $this->assertSame( u::strlen($c), u::strlen($d) );
        $this->assertSame( '', u::trim($d, $c) );
    }

    /**
     * @covers Patchwork\Utf8::str_split
     */
    function testStr_split()
    {
        $this->assertSame( array('d','é','j','à'), u::str_split('déjà', 1) );
        $this->assertSame( array('dé','jà'), u::str_split('déjà', 2) );
    }

    /**
     * @covers Patchwork\Utf8::str_word_count
     */
    function testStr_word_count()
    {
        $this->assertSame( array(0 => 'déjà', 5 => 'vu'), u::str_word_count('déjà vu', 2) );
    }

    /**
     * @covers Patchwork\Utf8::utf8_encode
     * @covers Patchwork\Utf8::utf8_decode
     */
    function testUtf8EncodeDecode()
    {
        $s = array_map('chr', range(0, 255));
        $s = implode('', $s);
        $e = u::utf8_encode($s);

        $this->assertSame( 1, preg_match('//u', $e) );
        $this->assertSame( $s, u::utf8_decode($e) );
    }
}
