<?php

namespace Patchwork\Tests\PHP\Shim;

use Patchwork\PHP\Shim\Xml as p;

/**
 * @covers Patchwork\PHP\Shim\Xml::<!public>
 */
class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\PHP\Shim\Xml::cp1252_to_utf8
     * @covers Patchwork\PHP\Shim\Xml::utf8_to_cp1252
     */
    function testUtf8ToCp1252()
    {
        $s = array_map('chr', range(0, 255));
        $s = implode('', $s);
        $e = p::cp1252_to_utf8($s);

        $this->assertSame( 1, preg_match('//u', $e) );
        $this->assertSame( $s, p::utf8_to_cp1252($e) );
    }

    /**
     * @covers Patchwork\PHP\Shim\Xml::utf8_encode
     * @covers Patchwork\PHP\Shim\Xml::utf8_decode
     */
    function testUtf8Encode()
    {
        $s = array_map('chr', range(0, 255));
        $s = implode('', $s);
        $e = p::utf8_encode($s) . 'Σ어';

        $this->assertSame( utf8_encode($s), p::utf8_encode($s) );
        $this->assertSame( utf8_decode($e), p::utf8_decode($e) );
    }
}
