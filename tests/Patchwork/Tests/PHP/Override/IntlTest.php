<?php

namespace Patchwork\Tests\PHP\Override;

use Patchwork\PHP\Override\Intl as i;
use Normalizer as n;

class IntlTest extends \PHPUnit_Framework_TestCase
{
    function testGrapheme_extract()
    {
        $this->assertSame( i::grapheme_extract('한국어', 2, GRAPHEME_EXTR_COUNT, 3, $next), '국어' );
        $this->assertSame( $next, 9 );

        $this->assertSame( grapheme_extract('한국어', 2, GRAPHEME_EXTR_COUNT, 3, $next), '국어' );
        $this->assertSame( $next, 9 );
    }

    function testGrapheme_strlen()
    {
        $this->assertSame( i::grapheme_strlen('한국어'), 3 );
        $this->assertSame( i::grapheme_strlen(n::normalize('한국어', n::NFD)), 3 );
    }

    function testGrapheme_substr()
    {
        $c = "déjà";

        $this->assertSame( i::grapheme_substr($c, -2, 2), "jà" );
        $this->assertSame( i::grapheme_substr($c, -2, 3), "jà" );

        $this->assertSame( grapheme_substr($c, -2, 2), "jà" );
        $this->assertSame( grapheme_substr($c, -2, 3), false ); // Should be "jà", but grapheme_substr() is buggy
    }

    function testGrapheme_strpos()
    {
        $this->assertSame( i::grapheme_strpos('한국어', '국'), 1 );
        $this->assertSame( i::grapheme_stripos('DÉJÀ', 'à'), 3 );
    }
}
