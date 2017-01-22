<?php

namespace Patchwork\Tests\PHP\Shim;

use Patchwork\PHP\Shim\Normalizer as pn;
use Normalizer as in;

/**
 * @covers Patchwork\PHP\Shim\Normalizer::<!public>
 */
class NormalizerTest extends \PHPUnit_Framework_TestCase
{
    public $unicodeVersion = 70;

    public function testConstants()
    {
        $rpn = new \ReflectionClass('Patchwork\PHP\Shim\Normalizer');
        $rin = new \ReflectionClass('Normalizer');

        $rpn = $rpn->getConstants();
        $rin = $rin->getConstants();

        ksort($rpn);
        ksort($rin);

        $this->assertSame($rin, $rpn);
    }

    /**
     * @covers Patchwork\PHP\Shim\Normalizer::isNormalized
     */
    public function testIsNormalized()
    {
        $c = 'déjà';
        $d = in::normalize($c, pn::NFD);

        $this->assertTrue(pn::isNormalized(''));
        $this->assertTrue(pn::isNormalized('abc'));
        $this->assertTrue(pn::isNormalized($c));
        $this->assertTrue(pn::isNormalized($c, pn::NFC));
        $this->assertFalse(pn::isNormalized($d, pn::NFD)); // The current implementation defensively says false
        $this->assertFalse(pn::isNormalized($c, pn::NFD));
        $this->assertFalse(pn::isNormalized($d, pn::NFC));
        $this->assertFalse(pn::isNormalized("\xFF"));
        $this->assertFalse(pn::isNormalized('', pn::NONE));
        $this->assertFalse(pn::isNormalized('', 6));
    }

    /**
     * @covers Patchwork\PHP\Shim\Normalizer::normalize
     */
    public function testNormalize()
    {
        $c = in::normalize('déjà', pn::NFC).in::normalize('훈쇼™', pn::NFD);
        $this->assertSame($c, pn::normalize($c, pn::NONE));
        $this->assertSame($c, in::normalize($c, pn::NONE));

        $c = 'déjà 훈쇼™';
        $d = in::normalize($c, pn::NFD);
        $kc = in::normalize($c, pn::NFKC);
        $kd = in::normalize($c, pn::NFKD);

        $this->assertSame('', pn::normalize(''));
        $this->assertSame($c, pn::normalize($d));
        $this->assertSame($c, pn::normalize($d, pn::NFC));
        $this->assertSame($d, pn::normalize($c, pn::NFD));
        $this->assertSame($kc, pn::normalize($d, pn::NFKC));
        $this->assertSame($kd, pn::normalize($c, pn::NFKD));

        $this->assertFalse(pn::normalize($c, -1));
        $this->assertFalse(pn::normalize("\xFF"));

        $this->assertSame("\xcc\x83\xc3\x92\xd5\x9b", pn::normalize("\xcc\x83\xc3\x92\xd5\x9b"));
        $this->assertSame("\xe0\xbe\xb2\xe0\xbd\xb1\xe0\xbe\x80\xe0\xbe\x80", pn::normalize("\xe0\xbd\xb6\xe0\xbe\x81", pn::NFD));
    }

    /**
     * @covers Patchwork\PHP\Shim\Normalizer::normalize
     * @group unicode
     */
    public function testNormalizeConformance()
    {
        $t = file(__DIR__.'/NormalizationTest.'.$this->unicodeVersion.'.txt');
        $c = array();

        foreach ($t as $s) {
            $t = explode('#', $s);
            $t = explode(';', $t[0]);

            if (6 === count($t)) {
                foreach ($t as $k => $s) {
                    $t = explode(' ', $s);
                    $t = array_map('hexdec', $t);
                    $t = array_map('Patchwork\Utf8::chr', $t);
                    $c[$k] = implode('', $t);
                }

                $this->assertSame($c[1], pn::normalize($c[0], pn::NFC));
                $this->assertSame($c[1], pn::normalize($c[1], pn::NFC));
                $this->assertSame($c[1], pn::normalize($c[2], pn::NFC));
                $this->assertSame($c[3], pn::normalize($c[3], pn::NFC));
                $this->assertSame($c[3], pn::normalize($c[4], pn::NFC));

                $this->assertSame($c[2], pn::normalize($c[0], pn::NFD));
                $this->assertSame($c[2], pn::normalize($c[1], pn::NFD));
                $this->assertSame($c[2], pn::normalize($c[2], pn::NFD));
                $this->assertSame($c[4], pn::normalize($c[3], pn::NFD));
                $this->assertSame($c[4], pn::normalize($c[4], pn::NFD));

                $this->assertSame($c[3], pn::normalize($c[0], pn::NFKC));
                $this->assertSame($c[3], pn::normalize($c[1], pn::NFKC));
                $this->assertSame($c[3], pn::normalize($c[2], pn::NFKC));
                $this->assertSame($c[3], pn::normalize($c[3], pn::NFKC));
                $this->assertSame($c[3], pn::normalize($c[4], pn::NFKC));

                $this->assertSame($c[4], pn::normalize($c[0], pn::NFKD));
                $this->assertSame($c[4], pn::normalize($c[1], pn::NFKD));
                $this->assertSame($c[4], pn::normalize($c[2], pn::NFKD));
                $this->assertSame($c[4], pn::normalize($c[3], pn::NFKD));
                $this->assertSame($c[4], pn::normalize($c[4], pn::NFKD));
            }
        }
    }
}
