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
        $this->assertSame( 'i', u::toAscii('ı') );

        $l = setlocale(LC_CTYPE, '0');

        if ('glibc' === ICONV_IMPL && 'de_DE.utf8' === setlocale(LC_CTYPE, 'de_DE.utf8', '0'))
        {
            $this->assertSame( 'ae', u::toAscii('ä') );
        }

        setlocale(LC_CTYPE, $l);
    }

    /**
     * @covers Patchwork\Utf8::strtocasefold
     */
    function testStrtocasefold()
    {
        $this->assertSame( 'σσσ', u::strtocasefold('Σσς') );
        $this->assertSame( 'iıi̇i', u::strtocasefold('Iıİi') );
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
        $this->assertSame( 'déjà σσς iıii', u::strtolower('DÉJÀ Σσς Iıİi') );
        $this->assertSame( 'DÉJÀ ΣΣΣ IIİI', u::strtoupper('Déjà Σσς Iıİi') );
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
     * @covers Patchwork\Utf8::strcasecmp
     * @covers Patchwork\Utf8::strnatcasecmp
     * @covers Patchwork\Utf8::strncasecmp
     * @covers Patchwork\Utf8::substr_compare
     */
    function testStrCmp()
    {
        $this->assertTrue( 0 !== u::strcasecmp('İ', 'i') );
        $this->assertTrue( 0 === u::strnatcasecmp('İ', 'i') );
        $this->assertTrue( 0 !== u::strncasecmp('İabc', 'idef', 1) );
        $this->assertTrue( 0 !== u::substr_compare('abcİdef', 'i', 3, 1, true) );

        $this->assertTrue( 0 !== u::strcasecmp('I', 'ı') );
        $this->assertTrue( 0 !== u::strnatcasecmp('I', 'ı') );
        $this->assertTrue( 0 !== u::strncasecmp('Iabc', 'ıdef', 1) );
        $this->assertTrue( 0 !== u::substr_compare('abcIdef', 'ı', 3, 1, true) );
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
        $text = "string\nwith\nnew\nlines";
        $this->assertSame( $text, u::wordwrap($text) );

        $text = "a  #b";
        $this->assertSame( wordwrap($text, 2, '#', false), u::wordwrap($text, 2, '#', false) );

        $text = 'A very long woooooooooooord.';

        $this->assertSame( wordwrap($text, 8, "\n", false), u::wordwrap($text, 8, "\n", false) );
        $this->assertSame( wordwrap($text, 8, "\n", true ), u::wordwrap($text, 8, "\n", true ) );

        $this->assertSame(
            str_replace(PHP_EOL, "\n",
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
oooooooooooooooooooooo"),
            u::wordwrap(
                "L’École supérieure de physique et de chimie industrielles de la ville de Paris, ou ESPCI ParisTech, est une grande école d’ingénieurs fondée en 1882. Elle est située rue Vauquelin sur la montagne Sainte-Geneviève dans le cinquième arrondissement de Paris. Yoooooooooooooooooooooooooooooooooooooooooooooo",
                25,
                "\n",
                true
            )
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
        $this->assertFalse( true, 'The only allowed $mode is 1' );
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
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    function testStr_pad()
    {
        $this->assertSame( 'ÉÈà-à-à-à-', u::str_pad('ÉÈ', 10, 'à-', STR_PAD_RIGHT) );
        $this->assertSame( 'à-à-à-à-ÉÈ', u::str_pad('ÉÈ', 10, 'à-', STR_PAD_LEFT ) );
        $this->assertSame( 'à-à-ÉÈà-à-', u::str_pad('ÉÈ', 10, 'à-', STR_PAD_BOTH ) );

        u::str_pad('ÉÈ', 10, 'à-', -1);
        $this->assertFalse( true, "Padding type has to be STR_PAD_LEFT, STR_PAD_RIGHT, or STR_PAD_BOTH" );
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
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    function testStr_split()
    {
        $this->assertSame( array('d','é','j','à'), u::str_split('déjà', 1) );
        $this->assertSame( array('dé','jà'), u::str_split('déjà', 2) );

        u::str_split('déjà', 0);
        $this->assertFalse( true, "The length of each segment must be greater than zero" );
    }

    /**
     * @covers Patchwork\Utf8::str_word_count
     */
    function testStr_word_count()
    {
        $this->assertSame( array(0 => 'déjà', 5 => 'vu'), u::str_word_count('déjà vu', 2) );
        $this->assertSame( 2, u::str_word_count('déjà vu', 0) );
        $this->assertSame( 2, u::str_word_count('déjà vu') );
    }

    /**
     * @covers Patchwork\Utf8::strtr
     */
    function testStrtr()
    {
        $this->assertSame( 'déja', u::strtr('dejà', 'eà', 'éa') );
    }

    /**
     * @covers Patchwork\Utf8::number_format
     */
    function testNumber_format()
    {
        $this->assertSame( '1×234¡56', u::number_format(1234.557, 2, '¡', '×') );
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

    /**
     * @covers Patchwork\Utf8::json_decode
     */
    function testJson_decode()
    {
        $c = "déjà";
        $d = n::normalize($c, n::NFD);
        $this->assertSame( $c, u::json_decode(json_encode($d)) );
        $this->assertSame( '◌' . n::normalize(substr($d, 2)), u::json_decode('"' . substr($d, 2) . '"') );
        $this->assertSame( "\n\n\n", u::json_decode('"\n\r\n\r"') );
    }

    /**
     * @covers Patchwork\Utf8::filter
     */
    function testFilter()
    {
        $c = "à";
        $d = n::normalize($c, n::NFD);

        $a = array(
            'n' => 4,
            'a' => "\xE9",
            'b' => substr($d, 1),
            'c' => $c,
            'd' => $d,
            'e' => "\n\r\n\r",
        );

        $a['f'] = (object) $a;

        $b = \Patchwork\Utf8::filter($a);
        $b['f'] = (array) $a['f'];

        $expect = array(
            'n' => 4,
            'a' => 'é',
            'b' => '◌' . substr($d, 1),
            'c' => $c,
            'd' => $c,
            'e' => "\n\n\n",
        );

        $expect['f'] = $expect;

        $this->assertSame($expect, $b);
    }
}
