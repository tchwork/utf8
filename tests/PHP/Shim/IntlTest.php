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
     * @covers Patchwork\PHP\Shim\Intl::grapheme_extract
     */
    public function testGrapheme_extract_arrayError()
    {
        try {
            p::grapheme_extract(array(), 0);
            $this->fail('Warning or notice expected');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->assertTrue(true, 'Regular PHP throws a warning');
        } catch (\PHPUnit_Framework_Error_Notice $e) {
            $this->assertTrue(true, 'HHVM throws a notice');
        }
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_extract
     */
    public function testGrapheme_extract()
    {
        $this->assertFalse(p::grapheme_extract('abc', 1, -1));

        $this->assertSame(grapheme_extract('',    0), p::grapheme_extract('',    0));
        $this->assertSame(grapheme_extract('abc', 0), p::grapheme_extract('abc', 0));

        $this->assertSame('국어', p::grapheme_extract('한국어', 2, GRAPHEME_EXTR_COUNT, 3, $next));
        $this->assertSame(9, $next);

        $this->assertSame('국어', grapheme_extract('한국어', 2, GRAPHEME_EXTR_COUNT, 3, $next));
        $this->assertSame(9, $next);

        $next = 0;
        $this->assertSame('한', p::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next));
        $this->assertSame('국', p::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next));
        $this->assertSame('어', p::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next));
        $this->assertFalse(p::grapheme_extract('한국어', 1, GRAPHEME_EXTR_COUNT, $next, $next));

        $this->assertSame(str_repeat('-', 69000), p::grapheme_extract(str_repeat('-', 70000), 69000, GRAPHEME_EXTR_COUNT));

        $this->assertSame('d', p::grapheme_extract('déjà', 2, GRAPHEME_EXTR_MAXBYTES));
        $this->assertSame('dé', p::grapheme_extract('déjà', 2, GRAPHEME_EXTR_MAXCHARS));

        $this->assertFalse(@p::grapheme_extract(array(), 0));
        $this->assertFalse(@grapheme_extract(array(), 0));
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_strlen
     */
    public function testGrapheme_strlen()
    {
        $this->assertSame(3, grapheme_strlen('한국어'));
        $this->assertSame(3, grapheme_strlen(n::normalize('한국어', n::NFD)));

        $this->assertSame(3, p::grapheme_strlen('한국어'));
        $this->assertSame(3, p::grapheme_strlen(n::normalize('한국어', n::NFD)));

        $this->assertNull(p::grapheme_strlen("\xE9"));
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_substr
     * @covers Patchwork\PHP\Shim\Intl::grapheme_substr_workaround62759
     */
    public function testGrapheme_substr()
    {
        $c = 'déjà';

        $this->assertSame('jà', grapheme_substr($c,  2));
        $this->assertSame('jà', grapheme_substr($c, -2));
        // The next 3 tests are disabled due to http://bugs.php.net/62759 and 55562
        //$this->assertSame( "jà", grapheme_substr($c, -2,  3) );
        //$this->assertSame( "", grapheme_substr($c, -1,  0) );
        //$this->assertSame( false, grapheme_substr($c,  1, -4) );
        $this->assertSame('j', grapheme_substr($c, -2, -1));
        $this->assertSame('', grapheme_substr($c, -2, -2));
        $this->assertSame(false, grapheme_substr($c,  5,  0));
        $this->assertSame(false, grapheme_substr($c, -5,  0));

        $this->assertSame('jà', p::grapheme_substr($c,  2));
        $this->assertSame('jà', p::grapheme_substr($c, -2));
        $this->assertSame('jà', p::grapheme_substr($c, -2, 3));
        $this->assertSame('', p::grapheme_substr($c, -1,  0));
        $this->assertSame(false, p::grapheme_substr($c,  1, -4));
        $this->assertSame('j', p::grapheme_substr($c, -2, -1));
        $this->assertSame('', p::grapheme_substr($c, -2, -2));
        $this->assertSame(false, p::grapheme_substr($c,  5,  0));
        $this->assertSame(false, p::grapheme_substr($c, -5,  0));

        $this->assertSame('jà', p::grapheme_substr_workaround62759($c,  2, 2147483647));
        $this->assertSame('jà', p::grapheme_substr_workaround62759($c, -2, 2147483647));
        $this->assertSame('jà', p::grapheme_substr_workaround62759($c, -2, 3));
        $this->assertSame('', p::grapheme_substr_workaround62759($c, -1,  0));
        $this->assertSame(false, p::grapheme_substr_workaround62759($c,  1, -4));
        $this->assertSame('j', p::grapheme_substr_workaround62759($c, -2, -1));
        $this->assertSame('', p::grapheme_substr_workaround62759($c, -2, -2));
        $this->assertSame(false, p::grapheme_substr_workaround62759($c,  5,  0));
        $this->assertSame(false, p::grapheme_substr_workaround62759($c, -5,  0));
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_strpos
     * @covers Patchwork\PHP\Shim\Intl::grapheme_stripos
     * @covers Patchwork\PHP\Shim\Intl::grapheme_strrpos
     * @covers Patchwork\PHP\Shim\Intl::grapheme_strripos
     * @covers Patchwork\PHP\Shim\Intl::grapheme_position
     */
    public function testGrapheme_strpos()
    {
        $this->assertSame(false, grapheme_strpos('abc', ''));
        $this->assertSame(false, grapheme_strpos('abc', 'd'));
        $this->assertSame(false, grapheme_strpos('abc', 'a', 3));
        $this->assertSame(0, grapheme_strpos('abc', 'a', -1));
        $this->assertSame(1, grapheme_strpos('한국어', '국'));
        $this->assertSame(3, grapheme_stripos('DÉJÀ', 'à'));
        $this->assertSame(false, grapheme_strrpos('한국어', ''));
        $this->assertSame(1, grapheme_strrpos('한국어', '국'));
        $this->assertSame(3, grapheme_strripos('DÉJÀ', 'à'));

        $this->assertSame(false, p::grapheme_strpos('abc', ''));
        $this->assertSame(false, p::grapheme_strpos('abc', 'd'));
        $this->assertSame(false, p::grapheme_strpos('abc', 'a', 3));
        $this->assertSame(0, p::grapheme_strpos('abc', 'a', -1));
        $this->assertSame(1, p::grapheme_strpos('한국어', '국'));
        $this->assertSame(3, p::grapheme_stripos('DÉJÀ', 'à'));
        $this->assertSame(false, p::grapheme_strrpos('한국어', ''));
        $this->assertSame(1, p::grapheme_strrpos('한국어', '국'));
        $this->assertSame(3, p::grapheme_strripos('DÉJÀ', 'à'));
        $this->assertSame(16, p::grapheme_stripos('der Straße nach Paris', 'Paris'));
    }

    /**
     * @covers Patchwork\PHP\Shim\Intl::grapheme_strstr
     * @covers Patchwork\PHP\Shim\Intl::grapheme_stristr
     */
    public function testGrapheme_strstr()
    {
        $this->assertSame('국어', grapheme_strstr('한국어', '국'));
        $this->assertSame('ÉJÀ', grapheme_stristr('DÉJÀ', 'é'));

        $this->assertSame('국어', p::grapheme_strstr('한국어', '국'));
        $this->assertSame('ÉJÀ', p::grapheme_stristr('DÉJÀ', 'é'));
        $this->assertSame('Paris', p::grapheme_stristr('der Straße nach Paris', 'Paris'));
    }

    public function testGrapheme_bugs()
    {
        if (extension_loaded('intl') && (50418 > PHP_VERSION_ID || 50500 == PHP_VERSION_ID)) {
            // Buggy behavior see https://bugs.php.net/61860
            $this->assertSame(17, grapheme_stripos('der Straße nach Paris', 'Paris'));
            $this->assertSame('aris', grapheme_stristr('der Straße nach Paris', 'Paris'));
        } else {
            $this->assertSame(16, grapheme_stripos('der Straße nach Paris', 'Paris'));
            $this->assertSame('Paris', grapheme_stristr('der Straße nach Paris', 'Paris'));
        }
    }
}
