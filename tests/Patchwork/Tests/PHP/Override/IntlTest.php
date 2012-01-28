<?php

namespace Patchwork\Tests\PHP\Override;

use Patchwork\PHP\Override\Intl as i;

class IntlTest extends \PHPUnit_Framework_TestCase
{
    function testGrapheme_substr()
    {
        $c = "déjà";

        $this->assertSame( i::grapheme_substr($c, -2, 2), "jà" );
        $this->assertSame( i::grapheme_substr($c, -2, 3), "jà" );

        $this->assertSame( grapheme_substr($c, -2, 2), "jà" );

        $this->markTestSkipped( 'Skip testing grapheme_substr() with over limit $length parameter' );

        $this->assertSame( grapheme_substr($c, -2, 3), "jà" );
    }
}
