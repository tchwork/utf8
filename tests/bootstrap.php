<?php

$dir = dirname(dirname(__FILE__));

require $dir . '/class/Patchwork/Utf8/Compiler.php';
require $dir . '/class/Patchwork/Utf8/Normalizer.php';
require $dir . '/class/Patchwork/PHP/Override/Intl.php';
require $dir . '/class/Patchwork/PHP/Override/Iconv.php';
require $dir . '/class/Patchwork/PHP/Override/WinfsUtf8.php';
require $dir . '/class/Patchwork/PHP/Override/Mbstring8bit.php';
require $dir . '/class/Patchwork/PHP/Override/Mbstring.php';
require $dir . '/class/Patchwork/PHP/Override/Utf8.php';
require $dir . '/class/Patchwork/Utf8.php';
