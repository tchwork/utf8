<?php

$dir = dirname(dirname(__FILE__));

require $dir . '/bootup.utf8.php';

require_once $dir . '/class/Patchwork/Utf8.php';
require_once $dir . '/class/Patchwork/PHP/Shim/Xml.php';
require_once $dir . '/class/Patchwork/PHP/Shim/Intl.php';
require_once $dir . '/class/Patchwork/PHP/Shim/Iconv.php';
require_once $dir . '/class/Patchwork/PHP/Shim/Mbstring.php';
require_once $dir . '/class/Patchwork/PHP/Shim/Normalizer.php';
