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
     */
    function testFilterRequestInputs()
    {
        $c = "à";
        $d = n::normalize($c, n::NFD);

        $bak = array($_GET, $_POST, $_COOKIE, $_REQUEST, $_ENV, $_FILES);

        $_GET = array(
            'a' => "\xE9",
            'b' => substr($d, 1),
            'c' => $c,
            'd' => $d,
        );

        $_GET['e'] = $_GET;

        \Patchwork\Utf8\Bootup::filterRequestInputs();

        $expect = array(
            'a' => 'é',
            'b' => '◌' . substr($d, 1),
            'c' => $c,
            'd' => $c,
        );

        $expect['e'] = $expect;

        $this->assertSame($expect, $_GET);

        list($_GET, $_POST, $_COOKIE, $_REQUEST, $_ENV, $_FILES) = $bak;
    }
}
