<?php

namespace Patchwork\Tests\Utf8;

use Patchwork\Utf8\Normalizer as pn;
use Normalizer as in;

class NormalizerTest extends \PHPUnit_Framework_TestCase
{
    function testIsNormalized()
    {
        $c = "déjà";
        $d = in::normalize($c, in::NFD);

        $this->assertTrue( pn::isNormalized($c, pn::NFC) );
        $this->assertFalse( pn::isNormalized($d, pn::NFD) ); // The current implementation defensively says false
        $this->assertFalse( pn::isNormalized($c, pn::NFD) );
        $this->assertFalse( pn::isNormalized($d, pn::NFC) );
    }

    function testNormalize()
    {
        $c = "déjà 훈쇼";
        $d = in::normalize($c, in::NFD);

        $this->assertSame( pn::normalize($d), $c );
    }
}
