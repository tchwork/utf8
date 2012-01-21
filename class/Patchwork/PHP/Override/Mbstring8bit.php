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

namespace Patchwork\PHP\Override;

/**
 * Binary safe version of string functions overloaded when MB_OVERLOAD_STRING is enabled.
 */
class Mbstring8bit
{
    static function mail($to, $subject, $message, $additional_headers = '', $additional_parameters = '')
    {
        return mb_send_mail($to, $subject, $message, $additional_headers, $additional_parameters, '8bit');
    }

    static function strlen($s)
    {
        return mb_strlen($s, '8bit');
    }

    static function strpos($haystack, $needle, $offset = 0)
    {
        return mb_strpos($haystack, $needle, $offset, '8bit');
    }

    static function strrpos($haystack, $needle, $offset = 0)
    {
        return mb_strrpos($haystack, $needle, $offset, '8bit');
    }

    static function substr($string, $start, $length = 2147483647)
    {
        return mb_substr($string, $start, $length, '8bit');
    }

    static function stripos($s, $needle, $offset = 0)
    {
        return mb_stripos($s, $needle, $offset, '8bit');
    }

    static function stristr($s, $needle, $part = false)
    {
        return mb_stristr($s, $needle, $part, '8bit');
    }

    static function strrchr($s, $needle, $part = false)
    {
        return mb_strrchr($s, $needle, $part, '8bit');
    }

    static function strripos($s, $needle, $offset = 0)
    {
        return mb_strripos($s, $needle, $offset, '8bit');
    }

    static function strstr($s, $needle, $part = false)
    {
        return mb_strstr($s, $needle, $part, '8bit');
    }
}
