<?php /****************** vi: set fenc=utf-8 ts=4 sw=4 et: *****************
 *
 *   Copyright : (C) 2011 Nicolas Grekas. All rights reserved.
 *   Email     : p@tchwork.org
 *   License   : http://www.gnu.org/licenses/lgpl.txt GNU/LGPL
 *
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public
 *   License as published by the Free Software Foundation; either
 *   version 3 of the License, or (at your option) any later version.
 *
 ***************************************************************************/

use Patchwork\PHP\Override as o;

require __DIR__ . '/class/Patchwork/Utf8.php';
require __DIR__ . '/class/Patchwork/PHP/Override/Utf8.php';

// utf8_encode/decode support enhanced to Windows-1252

function utf8_cp1252_encode($s) {return o\Utf8::utf8_encode($s);};
function utf8_cp1252_decode($s) {return o\Utf8::utf8_decode($s);};

if (!extension_loaded('xml'))
{
    function utf8_encode($s) {return o\Utf8::utf8_encode($s);};
    function utf8_decode($s) {return o\Utf8::utf8_decode($s);};
}


// mbstring configuration

if (extension_loaded('mbstring'))
{
    if ( in_array(strtolower(ini_get('mbstring.encoding_translation')), array(true, 'on', 'yes', 'true'))
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
    if ('UTF-8//IGNORE' !== iconv_get_encoding('input_encoding'))
        iconv_set_encoding('input_encoding', 'UTF-8//IGNORE') + ini_set('iconv.input_encoding', 'UTF-8//IGNORE');

    if ('UTF-8//IGNORE' !== iconv_get_encoding('internal_encoding'))
        iconv_set_encoding('internal_encoding', 'UTF-8//IGNORE') + ini_set('iconv.internal_encoding', 'UTF-8//IGNORE');

    if ('UTF-8//IGNORE' !== iconv_get_encoding('output_encoding'))
        iconv_set_encoding('output_encoding' , 'UTF-8//IGNORE') + ini_set('iconv.output_encoding', 'UTF-8//IGNORE');
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
    function iconv_mime_decode_headers($encoded_headers, $mode = 2, $charset = INF) {return o\Iconv::iconv_mime_decode_headers($encoded_headers, $mode, $charset);};

    if (extension_loaded('mbstring'))
    {
        function iconv_strlen($s, $enc = INF) {return mb_strlen($s, $enc);};
        function iconv_strpos($s, $needle, $offset = 0, $enc = INF) {return mb_strpos($s, $needle, $offset, $enc);};
        function iconv_strrpos($s, $needle, $enc = INF) {return mb_strrpos($s, $needle, $enc);};
        function iconv_substr($s, $start, $length = 2147483647, $enc = INF) {return mb_substr($s, $start, $length, $enc);};
        function iconv_mime_decode($encoded_headers, $mode = 2, $charset = INF) {return mb_decode_mimeheader($encoded_headers, $mode, $charset);};
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
        function iconv_mime_decode($encoded_headers, $mode = 2, $charset = INF) {return o\Iconv::iconv_mime_decode($encoded_headers, $mode, $charset);};
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
    require __DIR__ . '/class/Patchwork/Utf8/Normalizer.php';
    require __DIR__ . '/class/Patchwork/PHP/Override/Intl.php';

    class Normalizer extends Patchwork\Utf8\Normalizer {}

    function normalizer_is_normalized($s, $form = 'NFC') {return Patchwork\Utf8\Normalizer::isNormalized($s, $form);};
    function normalizer_normalize($s, $form = 'NFC') {return Patchwork\Utf8\Normalizer::normalize($s, $form);};

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
