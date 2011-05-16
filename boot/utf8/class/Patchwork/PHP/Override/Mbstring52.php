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


/*
 * Partial mbstring implementation in pure PHP
 *
 * All functions introduced in PHP 5.2.0:

mb_stripos  - Finds position of first occurrence of a string within another, case insensitive
mb_stristr  - Finds first occurrence of a string within another, case insensitive
mb_strrchr  - Finds the last occurrence of a character in a string within another
mb_strrichr - Finds the last occurrence of a character in a string within another, case insensitive
mb_strripos - Finds position of last occurrence of a string within another, case insensitive
mb_strstr   - Finds first occurrence of a string within another

 */

class Patchwork_PHP_Override_Mbstring52
{
    static function mb_stripos($haystack, $needle, $offset = 0, $encoding = INF)
    {
        INF === $encoding && $encoding = mb_internal_encoding();
        return mb_strpos(mb_strtolower($haystack, $encoding), mb_strtolower($needle, $encoding), $offset, $encoding);
    }

    static function mb_stristr($haystack, $needle, $part = false, $encoding = INF)
    {
        $pos = self::mb_stripos($haystack, $needle, $encoding);
        return self::getSubpart($pos, $part, $haystack, $encoding);
    }

    static function mb_strrchr($haystack, $needle, $part = false, $encoding = INF)
    {
        $needle = mb_substr($needle, 0, 1, $encoding);
        $pos = self::mb_strrpos($haystack, $needle, 0, $encoding);
        return self::getSubpart($pos, $part, $haystack, $encoding);
    }

    static function mb_strrichr($haystack, $needle, $part = false, $encoding = INF)
    {
        $needle = mb_substr($needle, 0, 1, $encoding);
        $pos = self::mb_strripos($haystack, $needle, $encoding);
        return self::getSubpart($pos, $part, $haystack, $encoding);
    }

    static function mb_strripos($haystack, $needle, $offset = 0, $encoding = INF)
    {
        INF === $encoding && $encoding = mb_internal_encoding();
        return self::mb_strrpos(mb_strtolower($haystack, $encoding), mb_strtolower($needle, $encoding), $offset, $encoding);
    }

    static function mb_strstr($haystack, $needle, $part = false, $encoding = INF)
    {
        $pos = strpos($haystack, $needle);
        return false === $pos ? false : ($part ? substr($haystack, 0, $pos) : substr($haystack, $pos));
    }

    static function mb_strrpos($haystack, $needle, $offset = 0, $encoding = INF)
    {
        INF === $encoding && $encoding = mb_internal_encoding();

        if ($offset != (int) $offset)
        {
            $offset = 0;
        }
        else if ($offset = (int) $offset)
        {
            $haystack = mb_substr($haystack, $offset, PHP_INT_MAX, $encoding);
        }

        $pos = mb_strrpos50($haystack, $needle, $encoding);

        return false !== $pos ? $offset + $pos : false;
    }


    protected static function getSubpart($pos, $part, $haystack, $encoding)
    {
        INF === $encoding && $encoding = mb_internal_encoding();

        return false === $pos ? false : (
              $part
            ? mb_substr($haystack,    0,        $pos, $encoding)
            : mb_substr($haystack, $pos, PHP_INT_MAX, $encoding)
        );
    }
}

/**/if (!function_exists('mb_strrpos50'))
/**/{
        function mb_strrpos50($haystack, $needle, $encoding = INF)
        {
            INF === $encoding && $encoding = mb_internal_encoding();
            return iconv_strrpos($haystack, $needle, $encoding);
        }
/**/}
