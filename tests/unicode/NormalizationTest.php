<?php

//use Normalizer as n;
use Patchwork\PHP\Shim\Normalizer as n;

/**
 * @covers Patchwork\PHP\Shim\Normalizer::<!public>
 */
class NormalizationTest extends \PHPUnit_Framework_TestCase
{
    public $unicodeVersion = 61;

    /**
     * @covers Patchwork\PHP\Shim\Normalizer::normalize
     */
    function testNormalize()
    {
        $t = file(__DIR__ . '/NormalizationTest.' . $this->unicodeVersion . '.txt');
        $c = array();

        foreach ($t as $s)
        {
            $t = explode("#", $s);
            $t = explode(';', $t[0]);

            if (6 === count($t))
            {
                foreach ($t as $k => $s)
                {
                    $t = explode(' ', $s);
                    $t = array_map('hexdec', $t);
                    $t = array_map('Patchwork\Utf8::chr', $t);
                    $c[$k] = implode('', $t);
                }

                $this->assertSame($c[1], n::normalize($c[0], n::NFC));
                $this->assertSame($c[1], n::normalize($c[1], n::NFC));
                $this->assertSame($c[1], n::normalize($c[2], n::NFC));
                $this->assertSame($c[3], n::normalize($c[3], n::NFC));
                $this->assertSame($c[3], n::normalize($c[4], n::NFC));

                $this->assertSame($c[2], n::normalize($c[0], n::NFD));
                $this->assertSame($c[2], n::normalize($c[1], n::NFD));
                $this->assertSame($c[2], n::normalize($c[2], n::NFD));
                $this->assertSame($c[4], n::normalize($c[3], n::NFD));
                $this->assertSame($c[4], n::normalize($c[4], n::NFD));

                $this->assertSame($c[3], n::normalize($c[0], n::NFKC));
                $this->assertSame($c[3], n::normalize($c[1], n::NFKC));
                $this->assertSame($c[3], n::normalize($c[2], n::NFKC));
                $this->assertSame($c[3], n::normalize($c[3], n::NFKC));
                $this->assertSame($c[3], n::normalize($c[4], n::NFKC));

                $this->assertSame($c[4], n::normalize($c[0], n::NFKD));
                $this->assertSame($c[4], n::normalize($c[1], n::NFKD));
                $this->assertSame($c[4], n::normalize($c[2], n::NFKD));
                $this->assertSame($c[4], n::normalize($c[3], n::NFKD));
                $this->assertSame($c[4], n::normalize($c[4], n::NFKD));
            }
        }
    }
}
