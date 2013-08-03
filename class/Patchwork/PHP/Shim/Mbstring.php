<?php // vi: set fenc=utf-8 ts=4 sw=4 et:
/*
 * Copyright (C) 2013 Nicolas Grekas - p@tchwork.com
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the (at your option):
 * Apache License v2.0 (http://apache.org/licenses/LICENSE-2.0.txt), or
 * GNU General Public License v2.0 (http://gnu.org/licenses/gpl-2.0.txt).
 */

namespace Patchwork\PHP\Shim;

/**
 * Partial mbstring implementation in PHP, iconv based, UTF-8 centric.
 *
 * Implemented:
 * - mb_convert_encoding     - Convert character encoding
 * - mb_decode_mimeheader    - Decode string in MIME header field
 * - mb_encode_mimeheader    - Encode string for MIME header XXX NATIVE IMPLEMENTATION IS REALLY BUGGED
 * - mb_convert_case         - Perform case folding on a string
 * - mb_internal_encoding    - Set/Get internal character encoding
 * - mb_list_encodings       - Returns an array of all supported encodings
 * - mb_strlen               - Get string length
 * - mb_strpos               - Find position of first occurrence of string in a string
 * - mb_strrpos              - Find position of last occurrence of a string in a string
 * - mb_strtolower           - Make a string lowercase
 * - mb_strtoupper           - Make a string uppercase
 * - mb_substitute_character - Set/Get substitution character
 * - mb_substr               - Get part of string
 * - mb_stripos              - Finds position of first occurrence of a string within another, case insensitive
 * - mb_stristr              - Finds first occurrence of a string within another, case insensitive
 * - mb_strrchr              - Finds the last occurrence of a character in a string within another
 * - mb_strrichr             - Finds the last occurrence of a character in a string within another, case insensitive
 * - mb_strripos             - Finds position of last occurrence of a string within another, case insensitive
 * - mb_strstr               - Finds first occurrence of a string within anothers
 *
 * Not implemented:
 * - mb_check_encoding             - Check if the string is valid for the specified encoding
 * - mb_convert_kana               - Convert "kana" one from another ("zen-kaku", "han-kaku" and more)
 * - mb_convert_variables          - Convert character code in variable(s)
 * - mb_decode_numericentity       - Decode HTML numeric string reference to character
 * - mb_detect_encoding            - Detect character encoding
 * - mb_detect_order               - Set/Get character encoding detection order
 * - mb_encode_numericentity       - Encode character to HTML numeric string reference
 * - mb_ereg*                      - Regular expression with multibyte support
 * - mb_get_info                   - Get internal settings of mbstring
 * - mb_http_input                 - Detect HTTP input character encoding
 * - mb_http_output                - Set/Get HTTP output character encoding
 * - mb_language                   - Set/Get current language
 * - mb_list_encodings_alias_names - Returns an array of all supported alias encodings
 * - mb_list_mime_names            - Returns an array or string of all supported mime names
 * - mb_output_handler             - Callback function converts character encoding in output buffer
 * - mb_parse_str                  - Parse GET/POST/COOKIE data and set global variable
 * - mb_preferred_mime_name        - Get MIME charset string
 * - mb_regex_encoding             - Returns current encoding for multibyte regex as string
 * - mb_regex_set_options          - Set/Get the default options for mbregex functions
 * - mb_send_mail                  - Send encoded mail
 * - mb_split                      - Split multibyte string using regular expression
 * - mb_strcut                     - Get part of string
 * - mb_strimwidth                 - Get truncated string with specified width
 * - mb_strwidth                   - Return width of string
 * - mb_substr_count               - Count the number of substring occurrences
 */
class Mbstring
{
    const MB_CASE_FOLD = PHP_INT_MAX;

    protected static

    $internal_encoding = 'UTF-8',
    $caseFold = array(
        array('µ','ſ',"\xCD\x85",'ς',"\xCF\x90","\xCF\x91","\xCF\x95","\xCF\x96","\xCF\xB0","\xCF\xB1","\xCF\xB5","\xE1\xBA\x9B","\xE1\xBE\xBE"),
        array('μ','s','ι',       'σ','β',       'θ',       'φ',       'π',       'κ',       'ρ',       'ε',       "\xE1\xB9\xA1",'ι'           )
    );


    static function mb_convert_encoding($s, $to_encoding, $from_encoding = INF)
    {
        INF === $from_encoding && $from_encoding = self::$internal_encoding;

        if ('base64' === $from_encoding)
        {
            $s = base64_decode($s);
            $from_encoding = $to_encoding;
        }

        if ('base64' === $to_encoding) return base64_encode($s);

        if ('html-entities' === strtolower($to_encoding))
        {
            'html-entities' === $from_encoding && $from_encoding = 'Windows-1252';
            'utf-8' === $from_encoding || $s = iconv($from_encoding, 'UTF-8//IGNORE', $s);
            return preg_replace_callback('/[\x80-\xFF]+/', array(__CLASS__, 'html_encoding_callback'), $s);
        }

        if ('html-entities' === strtolower($from_encoding))
        {
            $s = html_entity_decode($s, ENT_COMPAT, 'UTF-8');
            $from_encoding = 'UTF-8';
        }

        return iconv($from_encoding, $to_encoding . '//IGNORE', $s);
    }

    static function mb_decode_mimeheader($s)
    {
        return iconv_mime_decode($s, 2, self::$internal_encoding . '//IGNORE');
    }

    static function mb_encode_mimeheader($s, $charset = INF, $transfer_encoding = INF, $linefeed = INF, $indent = INF)
    {
        user_error('mb_encode_mimeheader() is bugged. Please use iconv_mime_encode() instead.');
    }


    static function mb_convert_case($s, $mode, $encoding = INF)
    {
        if ('' === $s) return '';

        INF === $encoding && $encoding = self::$internal_encoding;
        if ('UTF-8' === strtoupper($encoding)) $encoding = INF;
        else $s = iconv($encoding, 'UTF-8//IGNORE', $s);

        if (MB_CASE_UPPER == $mode)
        {
            static $upper;
            isset($upper) || $upper = self::getData('upperCase');
            $map = $upper;
        }
        else
        {
            if (self::MB_CASE_FOLD === $mode) $s = str_replace(self::$caseFold[0], self::$caseFold[1], $s);

            static $lower;
            isset($lower) || $lower = self::getData('lowerCase');
            $map = $lower;
        }

        static $ulen_mask = array("\xC0" => 2, "\xD0" => 2, "\xE0" => 3, "\xF0" => 4);

        $i = 0;
        $len = strlen($s);

        while ($i < $len)
        {
            $ulen = $s[$i] < "\x80" ? 1 : $ulen_mask[$s[$i] & "\xF0"];
            $uchr = substr($s, $i, $ulen);
            $i += $ulen;

            if (isset($map[$uchr]))
            {
                $uchr = $map[$uchr];
                $nlen = strlen($uchr);

                if ($nlen == $ulen)
                {
                    $nlen = $i;
                    do $s[--$nlen] = $uchr[--$ulen];
                    while ($ulen);
                }
                else
                {
                    $s = substr_replace($s, $uchr, $i, $ulen);
                    $len += $nlen - $ulen;
                    $i   += $nlen - $ulen;
                }
            }
        }

        if (MB_CASE_TITLE == $mode)
        {
            $s = preg_replace_callback('/\b\p{Ll}/u', array(__CLASS__, 'title_case_callback'), $s);
        }

        if (INF === $encoding) return $s;
        else return iconv('UTF-8', $encoding, $s);
    }

    static function mb_internal_encoding($encoding = INF)
    {
        if (INF === $encoding) return self::$internal_encoding;

        if ('UTF-8' === strtoupper($encoding) || false !== @iconv($encoding, $encoding, ' '))
        {
            self::$internal_encoding = $encoding;
            return true;
        }

        return false;
    }

    static function mb_list_encodings()
    {
        return array('UTF-8');
    }

    static function mb_strlen($s, $encoding = INF)
    {
        INF === $encoding && $encoding = self::$internal_encoding;
        return iconv_strlen($s, $encoding . '//IGNORE');
    }

    static function mb_strpos ($haystack, $needle, $offset = 0, $encoding = INF)
    {
        INF === $encoding && $encoding = self::$internal_encoding;
        if ('' === (string) $needle)
        {
            user_error(__METHOD__ . ': Empty delimiter', E_USER_WARNING);
            return false;
        }
        else return iconv_strpos($haystack, $needle, $offset, $encoding . '//IGNORE');
    }

    static function mb_strrpos($haystack, $needle, $offset = 0, $encoding = INF)
    {
        INF === $encoding && $encoding = self::$internal_encoding;

        if ($offset != (int) $offset)
        {
            $offset = 0;
        }
        else if ($offset = (int) $offset)
        {
            $haystack = self::mb_substr($haystack, $offset, 2147483647, $encoding);
        }

        $pos = iconv_strrpos($haystack, $needle, $encoding . '//IGNORE');

        return false !== $pos ? $offset + $pos : false;
    }

    static function mb_strtolower($s, $encoding = INF)
    {
        return self::mb_convert_case($s, MB_CASE_LOWER, $encoding);
    }

    static function mb_strtoupper($s, $encoding = INF)
    {
        return self::mb_convert_case($s, MB_CASE_UPPER, $encoding);
    }

    static function mb_substitute_character($c = INF)
    {
        return INF !== $c ? false : 'none';
    }

    static function mb_substr($s, $start, $length = 2147483647, $encoding = INF)
    {
        INF === $encoding && $encoding = self::$internal_encoding;

        if ($start < 0)
        {
            $start = iconv_strlen($s, $encoding . '//IGNORE') + $start;
            if ($start < 0) $start = 0;
        }

        if ($length < 0)
        {
            $length = iconv_strlen($s, $encoding . '//IGNORE') + $length - $start;
            if ($length < 0) return '';
        }

        return (string) iconv_substr($s, $start, $length, $encoding . '//IGNORE');
    }

    static function mb_stripos($haystack, $needle, $offset = 0, $encoding = INF)
    {
        INF === $encoding && $encoding = self::$internal_encoding;
        $haystack = self::mb_convert_case($haystack, self::MB_CASE_FOLD, $encoding);
        $needle = self::mb_convert_case($needle, self::MB_CASE_FOLD, $encoding);
        return self::mb_strpos($haystack, $needle, $offset, $encoding);
    }

    static function mb_stristr($haystack, $needle, $part = false, $encoding = INF)
    {
        $pos = self::mb_stripos($haystack, $needle, $encoding);
        return self::getSubpart($pos, $part, $haystack, $encoding);
    }

    static function mb_strrchr($haystack, $needle, $part = false, $encoding = INF)
    {
        $needle = self::mb_substr($needle, 0, 1, $encoding);
        $pos = iconv_strrpos($haystack, $needle, $encoding);
        return self::getSubpart($pos, $part, $haystack, $encoding);
    }

    static function mb_strrichr($haystack, $needle, $part = false, $encoding = INF)
    {
        $needle = self::mb_substr($needle, 0, 1, $encoding);
        $pos = self::mb_strripos($haystack, $needle, $encoding);
        return self::getSubpart($pos, $part, $haystack, $encoding);
    }

    static function mb_strripos($haystack, $needle, $offset = 0, $encoding = INF)
    {
        INF === $encoding && $encoding = self::$internal_encoding;
        $haystack = self::mb_convert_case($haystack, self::MB_CASE_FOLD, $encoding);
        $needle = self::mb_convert_case($needle, self::MB_CASE_FOLD, $encoding);
        return self::mb_strrpos($haystack, $needle, $offset, $encoding);
    }

    static function mb_strstr($haystack, $needle, $part = false, $encoding = INF)
    {
        $pos = strpos($haystack, $needle);
        if (false === $pos) return false;
        if ($part) return substr($haystack, 0, $pos);
        else return substr($haystack, $pos);
    }


    protected static function getSubpart($pos, $part, $haystack, $encoding)
    {
        INF === $encoding && $encoding = self::$internal_encoding;

        if (false === $pos) return false;
        if ($part) return self::mb_substr($haystack,    0,       $pos, $encoding);
        else return self::mb_substr($haystack, $pos, 2147483647, $encoding);
    }

    protected static function html_encoding_callback($m)
    {
        return htmlentities($m, ENT_COMPAT, 'UTF-8');
    }

    protected static function title_case_callback($s)
    {
        return self::mb_convert_case($s[0], MB_CASE_UPPER, 'UTF-8');
    }

    protected static function getData($file)
    {
        $file = __DIR__ . '/unidata/' . $file . '.ser';
        if (file_exists($file)) return unserialize(file_get_contents($file));
        else return false;
    }
}
