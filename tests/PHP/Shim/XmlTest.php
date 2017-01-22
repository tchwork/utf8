<?php

namespace Patchwork\Tests\PHP\Shim;

use Patchwork\PHP\Shim\Xml as p;

/**
 * @covers Patchwork\PHP\Shim\Xml::<!public>
 */
class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\PHP\Shim\Xml::utf8_encode
     * @covers Patchwork\PHP\Shim\Xml::utf8_decode
     */
    public function testUtf8Encode()
    {
        $s = array_map('chr', range(0, 255));
        $s = implode('', $s);
        $e = p::utf8_encode($s);

        $this->assertSame(utf8_encode($s), p::utf8_encode($s));
        $this->assertSame(utf8_decode($e), p::utf8_decode($e));

        $this->assertSame('??', p::utf8_decode('Σ어'));

        $s = 444;

        $this->assertSame(utf8_encode($s), p::utf8_encode($s));
        $this->assertSame(utf8_decode($s), p::utf8_decode($s));
    }
}
