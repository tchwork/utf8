#!/usr/bin/php -q
<?php // vi: set fenc=utf-8 ts=4 sw=4 et:

use Patchwork\Utf8\Compiler as c;

$dir = dirname(dirname(__FILE__));

require_once $dir . '/class/Patchwork/Utf8/Compiler.php';

c::charsetMaps($dir . '/class/Patchwork/PHP/Shim/charset/');
c::translitMap($dir . '/class/Patchwork/PHP/Shim/charset/');

c::unicodeMaps($dir . '/class/Patchwork/PHP/Shim/unidata/');

rename($dir . '/class/Patchwork/PHP/Shim/unidata/caseFolding_full.ser', $dir . '/class/Patchwork/Utf8/data/caseFolding_full.ser');
