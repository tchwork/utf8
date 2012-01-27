<?php

namespace Patchwork\Tests;

use Patchwork\Utf8 as u;

class Utf8Test extends \PHPUnit_Framework_TestCase
{
    function testIsUtf8()
    {
        $this->assertTrue(u::isUtf8("abc"));
    }
}
