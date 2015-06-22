#!/usr/bin/php -q
<?php

use Patchwork\Utf8\Compiler as c;

$dir = dirname(dirname(__FILE__));

require_once $dir.'/src/Patchwork/Utf8/Compiler.php';

c::charsetMaps($dir.'/src/Patchwork/PHP/Shim/charset/');
c::translitMap($dir.'/src/Patchwork/PHP/Shim/charset/');
c::bestFit($dir.'/src/Patchwork/Utf8/data/');

c::unicodeMaps($dir.'/src/Patchwork/PHP/Shim/unidata/');

rename($dir.'/src/Patchwork/PHP/Shim/unidata/caseFolding_full.ser', $dir.'/src/Patchwork/Utf8/data/caseFolding_full.ser');
