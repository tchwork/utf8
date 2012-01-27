<?php

namespace Patchwork\Tests\PHP\Override;

use Patchwork\PHP\Override\Intl as i;

class IntlTest extends \PHPUnit_Framework_TestCase
{
    function testGrapheme_substr()
    {

        $c = "déjà";

        $this->assertEquals( grapheme_substr($c, -2, 2), "jà" );
        $this->assertFalse( grapheme_substr($c, -2, 3) );

        $this->assertEquals( i::grapheme_substr($c, -2, 2), "jà" );

        $this->markTestSkipped( "Skipping Intl::grapheme_substr() test with over limit length" );

        $this->assertFalse( i::grapheme_substr($c, -2, 3) );
    }
}
