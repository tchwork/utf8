<?php

namespace Patchwork\Tests;

use Patchwork\Utf8 as u;
use Normalizer;

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


    function testIsUtf8()
    {
        foreach (self::$utf8ValidityMap as $u => $t)
        {
            if ($t) $this->assertTrue( u::isUtf8($u) );
            else $this->assertFalse( u::isUtf8($u) );
        }
    }

    function testToASCII()
    {
        $this->assertEquals( u::toASCII('déjà vu'), 'deja vu' );
    }

    function testBestFit()
    {
        $this->assertEquals( u::bestFit(1252, 'déjà vu'), iconv('UTF-8', 'CP1252', 'déjà vu') );
    }

    function testStrtocasefold()
    {
        $this->assertEquals( u::strtocasefold('Σσς'), 'σσσ' );
    }

    function testStrtonatfold()
    {
        $this->assertEquals( u::strtonatfold('Déjà Σσς'), 'Deja Σσς' );
    }

    function testStrlen()
    {
        foreach (self::$utf8ValidityMap as $u => $t) if ($t)
        {
            $this->assertEquals( u::strlen($u), 1 );
        }

        $c = "déjà";
        $d = Normalizer::normalize("déjà", Normalizer::FORM_D);
        $this->assertTrue( $c > $d );

        $this->assertEquals( u::strlen($c), 4 );
        $this->assertEquals( u::strlen($c), u::strlen($d) );
    }

    function testSubstr()
    {
        $c = "déjà";
        $d = Normalizer::normalize("déjà", Normalizer::FORM_D);
        $this->assertTrue( $c > $d );

        $this->assertEquals( u::substr($c, 0, 2), "dé" );
        $this->assertEquals( Normalizer::normalize(u::substr($d, 0, 2), Normalizer::FORM_C), "dé" );

        $this->assertEquals( u::substr($c, -2), "jà");
        $this->assertEquals( Normalizer::normalize(u::substr($d, -2), Normalizer::FORM_C), "jà" );
    }
}
