<?php

namespace Patchwork\Tests;

use Patchwork\Utf8 as u;
use Normalizer as n;

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
     * @covers Patchwork\Utf8::toASCII
     */
    function testToASCII()
    {
        $this->assertSame( u::toASCII(''), '' );
        $this->assertSame( u::toASCII('déjà vu'), 'deja vu' );
    }

    /**
     * @covers Patchwork\Utf8::bestFit
     */
    function testBestFit()
    {
        $this->assertSame( u::bestFit(-1, ''), '' );
        $this->assertSame( u::bestFit(1252, 'déjà vu'), iconv('UTF-8', 'CP1252', 'déjà vu') );
    }

    /**
     * @covers Patchwork\Utf8::strtocasefold
     */
    function testStrtocasefold()
    {
        $this->assertSame( u::strtocasefold('Σσς'), 'σσσ' );
    }

    /**
     * @covers Patchwork\Utf8::strtonatfold
     */
    function testStrtonatfold()
    {
        $this->assertSame( u::strtonatfold('Déjà Σσς'), 'Deja Σσς' );
    }

    /**
     * @covers Patchwork\Utf8::substr
     */
    function testSubstr()
    {
        $b = "deja";
        $c = "déjà";
        $d = n::normalize("déjà", n::NFD);
        $this->assertTrue( $c > $d );

        $this->assertSame( u::substr('한국어', 1, 20), '국어' );

        $this->assertSame( substr($b,  0,  2), "de" );
        $this->assertSame( substr($b, -2,  3), "ja" );
        $this->assertSame( substr($b, -3, -1), "ej" );
        $this->assertSame( substr($b,  1, -3), "" );
        $this->assertSame( substr($c,  5,  0), "" ); // u::substr() returns false here
        $this->assertSame( substr($c, -5,  0), "" ); // u::substr() returns false here
        $this->assertSame( substr($b,  1, -4), false );

        $this->assertSame( u::substr($c,  2    ), "jà" );
        $this->assertSame( u::substr($c, -2    ), "jà" );
        $this->assertSame( u::substr($c,  0,  2), "dé" );
        $this->assertSame( u::substr($c, -2,  3), "jà" );
        $this->assertSame( u::substr($c, -3, -1), "éj" );
        $this->assertSame( u::substr($c,  1, -3), "" );
        $this->assertSame( u::substr($c,  5,  0), false ); // Modelled after grapheme_substr(), not substr() (see above)
        $this->assertSame( u::substr($c, -5,  0), false ); // Modelled after grapheme_substr(), not substr() (see above)
        $this->assertSame( u::substr($c,  1, -4), false );

        $this->assertSame( u::substr($d,  0,  2), n::normalize("dé", n::NFD) );
        $this->assertSame( u::substr($d, -2,  3), n::normalize("jà", n::NFD) );
        $this->assertSame( u::substr($d, -3, -1), n::normalize("éj", n::NFD) );
        $this->assertSame( u::substr($d,  1, -3), "" );
        $this->assertSame( u::substr($d,  1, -4), false );
    }

    /**
     * @covers Patchwork\Utf8::strlen
     */
    function testStrlen()
    {
        foreach (self::$utf8ValidityMap as $u => $t) if ($t)
        {
            $this->assertSame( u::strlen($u), 1 );
        }

        $c = "déjà";
        $d = n::normalize("déjà", n::NFD);
        $this->assertTrue( $c > $d );

        $this->assertSame( u::strlen($c), 4 );
        $this->assertSame( u::strlen($d), 4 );

        $this->assertSame( u::strlen(n::normalize('한국어', n::NFD)), 3 );
    }

    /**
     * @covers Patchwork\Utf8::strpos
     * @covers Patchwork\Utf8::stripos
     * @covers Patchwork\Utf8::strrpos
     * @covers Patchwork\Utf8::strripos
     */
    function testStrpos()
    {
        $this->assertSame( u::strpos('déjà', 'à'), 3 );
        $this->assertSame( u::stripos('DÉJÀ', 'à'), 3 );
        $this->assertSame( u::strrpos('déjà', 'é'), 1 );
        $this->assertSame( u::strripos('DÉJÀ', 'é'), 1 );
    }

    /**
     * @covers Patchwork\Utf8::strstr
     * @covers Patchwork\Utf8::stristr
     * @covers Patchwork\Utf8::strrchr
     * @covers Patchwork\Utf8::strrichr
     */
    function testStrstr()
    {
        $this->assertSame( u::strstr('déjàdéjà', 'é'), 'éjàdéjà' );
        $this->assertSame( u::stristr('DÉJÀDÉJÀ', 'é'), 'ÉJÀDÉJÀ' );
        $this->assertSame( u::strrchr('déjàdéjà', 'é'), 'éjà' );
        $this->assertSame( u::strrichr('DÉJÀDÉJÀ', 'é'), 'ÉJÀ' );

        $this->assertSame( u::strstr('déjàdéjà', 'é', true), 'd' );
        $this->assertSame( u::stristr('DÉJÀDÉJÀ', 'é', true), 'D' );
        $this->assertSame( u::strrchr('déjàdéjà', 'é', true), 'déjàd' );
        $this->assertSame( u::strrichr('DÉJÀDÉJÀ', 'é', true), 'DÉJÀD' );
    }

    /**
     * @covers Patchwork\Utf8::wordwrap
     */
    function testWordwrap()
    {
        $this->assertSame(
            u::wordwrap("L’École supérieure de physique et de chimie industrielles de la ville de Paris, ou ESPCI ParisTech, est une grande école d’ingénieurs fondée en 1882. Elle est située rue Vauquelin sur la montagne Sainte-Geneviève dans le cinquième arrondissement de Paris. Yoooooooooooooooooooooooooooooooooooooooooooooo", 25, "\n", true),

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
oooooooooooooooooooooo"
        );
    }

    /**
     * @covers Patchwork\Utf8::chr
     * @covers Patchwork\Utf8::ord
     */
    function testChrOrd()
    {
        foreach (self::$utf8ValidityMap as $u => $t) if ($t)
        {
            $this->assertSame( u::chr(u::ord($u)), $u );
        }
    }

    /**
     * @covers Patchwork\Utf8::str_pad
     */
    function testStr_pad()
    {
        $this->assertSame( u::str_pad('ÉÈ', 10, 'à-', STR_PAD_RIGHT), 'ÉÈà-à-à-à-');
        $this->assertSame( u::str_pad('ÉÈ', 10, 'à-', STR_PAD_LEFT ), 'à-à-à-à-ÉÈ');
        $this->assertSame( u::str_pad('ÉÈ', 10, 'à-', STR_PAD_BOTH ), 'à-à-ÉÈà-à-');
    }

    /**
     * @covers Patchwork\Utf8::str_split
     */
    function testStr_split()
    {
        $this->assertSame( u::str_split('déjà', 1), array('d','é','j','à') );
        $this->assertSame( u::str_split('déjà', 2), array('dé','jà') );
    }

    /**
     * @covers Patchwork\Utf8::str_word_count
     */
    function testStr_word_count()
    {
        $this->assertSame( u::str_word_count('déjà vu', 2), array(0 => 'déjà', 5 => 'vu') );
    }
}
