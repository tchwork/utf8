<?php

$dir = dirname(__DIR__);

require $dir . '/class/Patchwork/Utf8/Bootup.php';
require $dir . '/class/Patchwork/Utf8/BestFit.php';
require $dir . '/class/Patchwork/Utf8.php';
require $dir . '/class/Patchwork/TurkishUtf8.php';
require $dir . '/class/Patchwork/PHP/Shim/Xml.php';
require $dir . '/class/Patchwork/PHP/Shim/Intl.php';
require $dir . '/class/Patchwork/PHP/Shim/Iconv.php';
require $dir . '/class/Patchwork/PHP/Shim/Mbstring.php';
require $dir . '/class/Patchwork/PHP/Shim/Normalizer.php';

class_exists('Normalizer', false) or require $dir . '/class/Normalizer.php';

\Patchwork\Utf8\Bootup::initAll();
