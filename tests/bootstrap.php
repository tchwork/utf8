<?php

$dir = dirname(__DIR__);

require $dir.'/src/Patchwork/Utf8/Bootup.php';
require $dir.'/src/Patchwork/Utf8/BestFit.php';
require $dir.'/src/Patchwork/Utf8/WindowsStreamWrapper.php';
require $dir.'/src/Patchwork/Utf8.php';
require $dir.'/src/Patchwork/TurkishUtf8.php';
require $dir.'/src/Patchwork/PHP/Shim/Xml.php';
require $dir.'/src/Patchwork/PHP/Shim/Intl.php';
require $dir.'/src/Patchwork/PHP/Shim/Iconv.php';
require $dir.'/src/Patchwork/PHP/Shim/Mbstring.php';
require $dir.'/src/Patchwork/PHP/Shim/Normalizer.php';

class_exists('Normalizer', false) or require $dir.'/src/Normalizer.php';

\Patchwork\Utf8\Bootup::initAll();
