<?php // vi: set fenc=utf-8 ts=4 sw=4 et:
/*
 * Copyright (C) 2012 Nicolas Grekas - p@tchwork.com
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the (at your option):
 * Apache License v2.0 (http://apache.org/licenses/LICENSE-2.0.txt), or
 * GNU General Public License v2.0 (http://gnu.org/licenses/gpl-2.0.txt).
 */

use Patchwork\PHP\Override as o;

require __DIR__ . '/class/Patchwork/Utf8.php';
require __DIR__ . '/class/Patchwork/PHP/Override/Xml.php';

// utf8_encode/decode

if (!extension_loaded('xml'))
{
    function utf8_encode($s) {return o\Xml::utf8_encode($s);};
    function utf8_decode($s) {return o\Xml::utf8_decode($s);};
}

// Cleanup input data

call_user_func(function()
{
    // Ensures the URL is well formed UTF-8
    // When not, assumes ISO-8859-1 and redirects to the corresponding UTF-8 encoded URL

    if (isset($_SERVER['REQUEST_URI']) && !preg_match('//u', urldecode($a = $_SERVER['REQUEST_URI'])))
    {
        if ($a === utf8_decode($a))
        {
            $a = preg_replace_callback(
                '/(?:%[89A-F][0-9A-F])+/i',
                function($m) {return urlencode(utf8_encode(urldecode($m[0])));},
                $a
            );
        }
        else $a = '/';

        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $a);

        exit;
    }

    // Ensures inputs are well formed UTF-8
    // When not, assumes ISO-8859-1 and converts to UTF-8
    // Tests only values, not keys

    $a = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST, &$_ENV);
    foreach ($_FILES as &$v) $a[] = array(&$v['name'], &$v['type']);

    $len = count($a);
    for ($i = 0; $i < $len; ++$i)
    {
        foreach ($a[$i] as &$v)
        {
            if (is_array($v)) $a[$len++] =& $v;
            else if (!preg_match('//u', $v)) $v = utf8_encode($v);
        }

        reset($a[$i]);
        unset($a[$i]);
    }
});

// mbstring configuration

if (extension_loaded('mbstring'))
{
    if ( ((int) ini_get('mbstring.encoding_translation') || in_array(strtolower(ini_get('mbstring.encoding_translation')), array('on', 'yes', 'true')))
        && !in_array(strtolower(ini_get('mbstring.http_input')), array('pass', '8bit', 'utf-8')) )
            throw new Exception('Please disable "mbstring.encoding_translation" or set "mbstring.http_input" to "utf-8" or "pass"');

    mb_regex_encoding('UTF-8');
    ini_set('mbstring.script_encoding', 'pass');

    if ('utf-8' !== strtolower(mb_internal_encoding()))
        mb_internal_encoding('UTF-8') + ini_set('mbstring.internal_encoding', 'UTF-8');

    if ('none' !== strtolower(mb_substitute_character()))
        mb_substitute_character('none') + ini_set('mbstring.substitute_character', 'none');

    if (!in_array(strtolower(mb_http_output()), array('pass', '8bit')))
        mb_http_output('pass') + ini_set('mbstring.http_output', 'pass');

    if (!in_array(strtolower(mb_language()), array('uni', 'neutral')))
        mb_language('uni') + ini_set('mbstring.language', 'uni');
}
else
{
    require __DIR__ . '/class/Patchwork/PHP/Override/Mbstring.php';

    define('MB_OVERLOAD_MAIL', 1);
    define('MB_OVERLOAD_STRING', 2);
    define('MB_OVERLOAD_REGEX', 4);
    define('MB_CASE_UPPER', 0);
    define('MB_CASE_LOWER',1);
    define('MB_CASE_TITLE', 2);

    function mb_convert_encoding($s, $to, $from = INF) {return o\Mbstring::mb_convert_encoding($s, $to, $from);};
    function mb_decode_mimeheader($s) {return o\Mbstring::mb_decode_mimeheader($s);};
    function mb_encode_mimeheader($s, $charset = INF, $transfer_enc = INF, $lf = INF, $indent = INF) {return o\Mbstring::mb_encode_mimeheader($s, $charset, $transfer_enc, $lf, $indent);};
    function mb_convert_case($s, $mode, $enc = INF) {return o\Mbstring::mb_convert_case($s, $mode, $enc);};
    function mb_internal_encoding($enc = INF) {return o\Mbstring::mb_internal_encoding($enc);};
    function mb_list_encodings() {return o\Mbstring::mb_list_encodings();};
    function mb_parse_str($s, &$result = array()) {return parse_str($s, $result);};
    function mb_strlen($s, $enc = INF) {return o\Mbstring::mb_strlen($s, $enc);};
    function mb_strpos($s, $needle, $offset = 0, $enc = INF) {return o\Mbstring::mb_strpos($s, $needle, $offset, $enc);};
    function mb_strtolower($s, $enc = INF) {return o\Mbstring::mb_strtolower($s, $enc);};
    function mb_strtoupper($s, $enc = INF) {return o\Mbstring::mb_strtoupper($s, $enc);};
    function mb_substitute_character($char = INF) {return o\Mbstring::mb_substitute_character($char);};
    function mb_substr_count($s, $needle) {return substr_count($s, $needle);};
    function mb_substr($s, $start, $length = 2147483647, $enc = INF) {return o\Mbstring::mb_substr($s, $start, $length, $enc);};
    function mb_stripos($s, $needle, $offset = 0, $enc = INF) {return o\Mbstring::mb_stripos($s, $needle, $offset, $enc);};
    function mb_stristr($s, $needle, $part = false, $enc = INF) {return o\Mbstring::mb_stristr($s, $needle, $part, $enc);};
    function mb_strrchr($s, $needle, $part = false, $enc = INF) {return o\Mbstring::mb_strrchr($s, $needle, $part, $enc);};
    function mb_strrichr($s, $needle, $part = false, $enc = INF) {return o\Mbstring::mb_strrichr($s, $needle, $part, $enc);};
    function mb_strripos($s, $needle, $offset = 0, $enc = INF) {return o\Mbstring::mb_strripos($s, $needle, $offset, $enc);};
    function mb_strrpos($s, $needle, $offset = 0, $enc = INF) {return o\Mbstring::mb_strrpos($s, $needle, $offset, $enc);};
    function mb_strstr($s, $needle, $part = false, $enc = INF) {return o\Mbstring::mb_strstr($s, $needle, $part, $enc);};
}


// iconv configuration

 // See http://php.net/manual/en/function.iconv.php#47428
if (!function_exists('iconv') && function_exists('libiconv'))
{
    function iconv($from, $to, $s) {return libiconv($from, $to, $s);};
}

if (extension_loaded('iconv'))
{
    if ('UTF-8' !== iconv_get_encoding('input_encoding'))
        iconv_set_encoding('input_encoding', 'UTF-8') + ini_set('iconv.input_encoding', 'UTF-8');

    if ('UTF-8' !== iconv_get_encoding('internal_encoding'))
        iconv_set_encoding('internal_encoding', 'UTF-8') + ini_set('iconv.internal_encoding', 'UTF-8');

    if ('UTF-8' !== iconv_get_encoding('output_encoding'))
        iconv_set_encoding('output_encoding' , 'UTF-8') + ini_set('iconv.output_encoding', 'UTF-8');
}
else
{
    require __DIR__ . '/class/Patchwork/PHP/Override/Iconv.php';

    define('ICONV_IMPL', 'Patchwork');
    define('ICONV_VERSION', '1.0');
    define('ICONV_MIME_DECODE_STRICT', 1);
    define('ICONV_MIME_DECODE_CONTINUE_ON_ERROR', 2);

    function iconv($from, $to, $s) {return o\Iconv::iconv($from, $to, $s);};
    function iconv_get_encoding($type = 'all') {return o\Iconv::iconv_get_encoding($type);};
    function iconv_set_encoding($type, $charset) {return o\Iconv::iconv_set_encoding($type, $charset);};
    function iconv_mime_encode($name, $value, $pref = INF) {return o\Iconv::iconv_mime_encode($name, $value, $pref);};
    function ob_iconv_handler($buffer, $mode) {return o\Iconv::ob_iconv_handler($buffer, $mode);};
    function iconv_mime_decode_headers($encoded_headers, $mode = 0, $charset = INF) {return o\Iconv::iconv_mime_decode_headers($encoded_headers, $mode, $charset);};

    if (extension_loaded('mbstring'))
    {
        function iconv_strlen($s, $enc = INF) {return mb_strlen($s, $enc);};
        function iconv_strpos($s, $needle, $offset = 0, $enc = INF) {return mb_strpos($s, $needle, $offset, $enc);};
        function iconv_strrpos($s, $needle, $enc = INF) {return mb_strrpos($s, $needle, $enc);};
        function iconv_substr($s, $start, $length = 2147483647, $enc = INF) {return mb_substr($s, $start, $length, $enc);};
        function iconv_mime_decode($encoded_headers, $mode = 0, $charset = INF) {return mb_decode_mimeheader($encoded_headers, $mode, $charset);};
    }
    else
    {
        if (extension_loaded('xml'))
        {
            function iconv_strlen($s, $enc = INF) {return o\Iconv::strlen1($s, $enc);};
        }
        else
        {
            function iconv_strlen($s, $enc = INF) {return o\Iconv::strlen2($s, $enc);};
        }

        function iconv_strpos($s, $needle, $offset = 0, $enc = INF) {return o\Mbstring::mb_strpos($s, $needle, $offset, $enc);};
        function iconv_strrpos($s, $needle, $enc = INF) {return o\Mbstring::mb_strrpos($s, $needle, $enc);};
        function iconv_substr($s, $start, $length = 2147483647, $enc = INF) {return o\Mbstring::mb_substr($s, $start, $length, $enc);};
        function iconv_mime_decode($encoded_headers, $mode = 0, $charset = INF) {return o\Iconv::iconv_mime_decode($encoded_headers, $mode, $charset);};
    }
}


// EXIF configuration

if (extension_loaded('exif'))
{
    if (ini_get('exif.encode_unicode') && 'UTF-8' !== strtoupper(ini_get('exif.encode_unicode')))
        ini_set('exif.encode_unicode', 'UTF-8');

    if (ini_get('exif.encode_jis') && 'UTF-8' !== strtoupper(ini_get('exif.encode_jis')))
        ini_set('exif.encode_jis', 'UTF-8');
}


// Check PCRE

if (!preg_match('/^.$/u', '§')) throw new Exception('PCRE is not compiled with UTF-8 support');


// intl configuration

if (!extension_loaded('intl'))
{
    require __DIR__ . '/class/Patchwork/PHP/Override/Normalizer.php';
    require __DIR__ . '/class/Patchwork/PHP/Override/Intl.php';

    class Normalizer extends o\Normalizer {}

    function normalizer_is_normalized($s, $form = o\Normalizer::NFC) {return o\Normalizer::isNormalized($s, $form);};
    function normalizer_normalize($s, $form = o\Normalizer::NFC) {return o\Normalizer::normalize($s, $form);};

    define('GRAPHEME_EXTR_COUNT', 0);
    define('GRAPHEME_EXTR_MAXBYTES', 1);
    define('GRAPHEME_EXTR_MAXCHARS', 2);

    function grapheme_extract($s, $size, $type = 0, $start = 0, &$next = 0) {return o\Intl::grapheme_extract($s, $size, $type, $start, $next);};
    function grapheme_stripos($s, $needle, $offset = 0) {return o\Intl::grapheme_stripos($s, $needle, $offset);};
    function grapheme_stristr($s, $needle, $before_needle = false) {return o\Intl::grapheme_stristr($s, $needle, $before_needle);};
    function grapheme_strlen($s) {return o\Intl::grapheme_strlen($s);};
    function grapheme_strpos($s, $needle, $offset = 0) {return o\Intl::grapheme_strpos($s, $needle, $offset);};
    function grapheme_strripos($s, $needle, $offset = 0) {return o\Intl::grapheme_strripos($s, $needle, $offset);};
    function grapheme_strrpos($s, $needle, $offset = 0) {return o\Intl::grapheme_strrpos($s, $needle, $offset);};
    function grapheme_strstr($s, $needle, $before_needle = false) {return o\Intl::grapheme_strstr($s, $needle, $before_needle);};
    function grapheme_substr($s, $start, $len = 2147483647) {return o\Intl::grapheme_substr($s, $start, $len);};
}
else if ('à' === grapheme_substr('éà', 1, -2))
{
    // Loads o\Intl::grapheme_substr_workaround62759()
    // when the native grapheme_substr() is buggy
    // so that \Patchwork\Utf8::substr() can use it.
    require __DIR__ . '/class/Patchwork/PHP/Override/Intl.php';
}
