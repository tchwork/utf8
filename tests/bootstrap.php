<?php

$dir = dirname(dirname(__FILE__));

require $dir . '/bootup.utf8.php';

require_once $dir . '/class/Patchwork/Utf8.php';
require_once $dir . '/class/Patchwork/PHP/Override/Xml.php';
require_once $dir . '/class/Patchwork/PHP/Override/Intl.php';
require_once $dir . '/class/Patchwork/PHP/Override/Iconv.php';
require_once $dir . '/class/Patchwork/PHP/Override/Mbstring.php';
require_once $dir . '/class/Patchwork/PHP/Override/Normalizer.php';
