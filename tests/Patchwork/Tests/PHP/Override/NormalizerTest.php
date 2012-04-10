<?php

namespace Patchwork\Tests\PHP\Override;

use Patchwork\PHP\Override\Normalizer as pn;
use Normalizer as in;

class NormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\PHP\Override\Normalizer::isNormalized
     */
    function testIsNormalized()
    {
        $c = "déjà";
        $d = in::normalize($c, pn::NFD);

        $this->assertTrue( pn::isNormalized('') );
        $this->assertTrue( pn::isNormalized('abc') );
        $this->assertTrue( pn::isNormalized($c) );
        $this->assertTrue( pn::isNormalized($c, pn::NFC) );
        $this->assertFalse( pn::isNormalized($d, pn::NFD) ); // The current implementation defensively says false
        $this->assertFalse( pn::isNormalized($c, pn::NFD) );
        $this->assertFalse( pn::isNormalized($d, pn::NFC) );
        $this->assertFalse( pn::isNormalized("\xFF") );
    }

    /**
     * @covers Patchwork\PHP\Override\Normalizer::normalize
     * @covers Patchwork\PHP\Override\Normalizer::decompose
     * @covers Patchwork\PHP\Override\Normalizer::recompose
     * @covers Patchwork\PHP\Override\Normalizer::getData
     */
    function testNormalize()
    {
        $c = in::normalize("déjà", pn::NFC) . in::normalize("훈쇼™", pn::NFD);
        $this->assertSame( pn::normalize($c, pn::NONE), $c );
        $this->assertSame( in::normalize($c, pn::NONE), $c );

        $c = "déjà 훈쇼™";
        $d = in::normalize($c, pn::NFD);
        $kc = in::normalize($c, pn::NFKC);
        $kd = in::normalize($c, pn::NFKD);

        $this->assertSame( pn::normalize(''), '' );
        $this->assertSame( pn::normalize($d), $c );
        $this->assertSame( pn::normalize($d, pn::NFC), $c );
        $this->assertSame( pn::normalize($c, pn::NFD), $d );
        $this->assertSame( pn::normalize($d, pn::NFKC), $kc );
        $this->assertSame( pn::normalize($c, pn::NFKD), $kd );

        $this->assertFalse( pn::normalize($c, -1) );
        $this->assertFalse( pn::normalize("\xFF") );
    }
}
