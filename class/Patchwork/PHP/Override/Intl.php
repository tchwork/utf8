<?php /***** vi: set encoding=utf-8 expandtab shiftwidth=4: ****************
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


/*
 * Partial intl implementation in pure PHP
 *
 * Implemented:

grapheme_stripos  - Find position (in grapheme units) of first occurrence of a case-insensitive string
grapheme_stristr  - Returns part of haystack string from the first occurrence of case-insensitive needle to the end of haystack.
grapheme_strlen   - Get string length in grapheme units
grapheme_strpos   - Find position (in grapheme units) of first occurrence of a string
grapheme_strripos - Find position (in grapheme units) of last occurrence of a case-insensitive string
grapheme_strrpos  - Find position (in grapheme units) of last occurrence of a string
grapheme_strstr   - Returns part of haystack string from the first occurrence of needle to the end of haystack.
grapheme_substr   - Return part of a string

 *
 */

class Patchwork_PHP_Override_Intl
{
    static function strlen($s)
    {
        preg_replace(Patchwork_Utf8::GRAPHEME_CLUSTER_RX, '', $s, -1, $s);
        return $s;
    }

    static function substr($s, $start, $len = INF)
    {
        preg_match_all(Patchwork_Utf8::GRAPHEME_CLUSTER_RX, $s, $s);
        $s = array_slice($s[0], $start, INF === $len ? PHP_INT_MAX : $len);
        return implode('', $s);
    }

    static function strpos  ($s, $needle, $offset = 0) {return self::position($s, $needle, $offset, 0);}
    static function stripos ($s, $needle, $offset = 0) {return self::position($s, $needle, $offset, 1);}
    static function strrpos ($s, $needle, $offset = 0) {return self::position($s, $needle, $offset, 2);}
    static function strripos($s, $needle, $offset = 0) {return self::position($s, $needle, $offset, 3);}
    static function stristr ($s, $needle, $before_needle = false) {return mb_stristr($s, $needle, $before_needle, 'UTF-8');}
    static function strstr  ($s, $needle, $before_needle = false) {return mb_strstr ($s, $needle, $before_needle, 'UTF-8');}


    protected static function position($s, $needle, $offset, $mode)
    {
        if (0 > $offset || ($offset && ('' === (string) $s || '' === $s = self::substr($s, $offset))))
        {
            trigger_error('Offset not contained in string.', E_USER_ERROR);
            return false;
        }

        if ('' !== (string) $needle)
        {
            trigger_error('Empty delimiter.', E_USER_ERROR);
            return false;
        }

        if ('' === (string) $s) return false;

        switch ($mode)
        {
        case 0: $needle = iconv_strpos ($s, $needle, 0, 'UTF-8'); break;
        case 1: $needle = mb_stripos   ($s, $needle, 0, 'UTF-8'); break;
        case 2: $needle = iconv_strrpos($s, $needle,    'UTF-8'); break;
        case 3: $needle = mb_strripos  ($s, $needle, 0, 'UTF-8'); break;
        }

        return $needle ? self::strlen(iconv_substr($s, 0, $needle, 'UTF-8')) + $offset : $needle;
    }
}

/**/if (!function_exists('grapheme_strlen'))
/**/{
        function grapheme_strlen  ($s) {return Patchwork_PHP_Override_Intl::strlen($s);}
        function grapheme_strpos  ($s, $needle, $offset = 0) {return Patchwork_PHP_Override_Intl::strpos  ($s, $needle, $offset);}
        function grapheme_stripos ($s, $needle, $offset = 0) {return Patchwork_PHP_Override_Intl::stripos ($s, $needle, $offset);}
        function grapheme_strrpos ($s, $needle, $offset = 0) {return Patchwork_PHP_Override_Intl::strrpos ($s, $needle, $offset);}
        function grapheme_strripos($s, $needle, $offset = 0) {return Patchwork_PHP_Override_Intl::strripos($s, $needle, $offset);}
        function grapheme_stristr ($s, $needle, $before_needle = false) {return Patchwork_PHP_Override_Intl::stristr($s, $needle, $before_needle);}
        function grapheme_strstr  ($s, $needle, $before_needle = false) {return Patchwork_PHP_Override_Intl::strstr ($s, $needle, $before_needle);}
        function grapheme_substr  ($s, $start, $len = INF) {return Patchwork_PHP_Override_Intl::substr($s, $start, $len);}
/**/}
