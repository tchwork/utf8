<?php

namespace Patchwork\Tests\Utf8;

use Normalizer as n;

/**
 * @covers Patchwork\Utf8\Bootup::<!public>
 */
class BootupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\Utf8\Bootup::filterRequestInputs
     * @covers Patchwork\Utf8\Bootup::filter
     */
    function testFilterRequestInputs()
    {
        $c = "à";
        $d = n::normalize($c, n::NFD);

        $bak = array($_GET, $_POST, $_COOKIE, $_REQUEST, $_ENV, $_FILES);

        $_GET = array(
            'n' => 4,
            'a' => "\xE9",
            'b' => substr($d, 1),
            'c' => $c,
            'd' => $d,
            'e' => "\n\r\n\r",
        );

        $_GET['f'] = $_GET;

        \Patchwork\Utf8\Bootup::filterRequestInputs();

        $expect = array(
            'n' => 4,
            'a' => 'é',
            'b' => '◌' . substr($d, 1),
            'c' => $c,
            'd' => $c,
            'e' => "\n\n\n",
        );

        $expect['f'] = $expect;

        $this->assertSame($expect, $_GET);

        list($_GET, $_POST, $_COOKIE, $_REQUEST, $_ENV, $_FILES) = $bak;
    }
}
