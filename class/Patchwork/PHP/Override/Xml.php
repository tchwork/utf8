<?php // vi: set fenc=utf-8 ts=4 sw=4 et:
/*
 * Copyright (C) 2012 Nicolas Grekas - p@tchwork.com
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the (at your option):
 * Apache License v2.0 (http://apache.org/licenses/LICENSE-2.0.txt), or
 * GNU General Public License v2.0 (http://gnu.org/licenses/gpl-2.0.txt).
 */

namespace Patchwork\PHP\Override;

/**/$map = array(
/**/    "\x80 \x82 \x83 \x84 \x85 \x86 \x87 \x88 \x89 \x8A \x8B \x8C \x8E \x91 \x92 \x93 \x94 \x95 \x96 \x97 \x98 \x99 \x9A \x9B \x9C \x9E \x9F",
/**/     '€    ‚    ƒ    „    …    †    ‡    ˆ    ‰    Š    ‹    Œ    Ž    ‘    ’    “    ”    •    –    —    ˜    ™    š    ›    œ    ž    Ÿ'
/**/);
/**/
/**/$map[0] = explode('-', "\xC2" . str_replace(' ', "-\xC2", $map[0]));
/**/$map[1] = explode('    ', $map[1]);

Xml::$cp1252 = /*<*/$map[0]/*>*/;
Xml::$utf8   = /*<*/$map[1]/*>*/;


/**
 * utf8_encode/decode enhanced to Windows-1252.
 */
class Xml
{
    static $cp1252, $utf8;

    static function cp1252_to_utf8($s)
    {
/**/    if (function_exists('utf8_encode'))
/**/    {
            $s = utf8_encode($s);
/**/    }
/**/    else
/**/    // @codeCoverageIgnoreStart
/**/    {
            $s = self::utf8_encode($s);
/**/    }
/**/    // @codeCoverageIgnoreEnd

        if (false === strpos($s, "\xC2")) return $s;
        else return str_replace(self::$cp1252, self::$utf8, $s);
    }

    static function utf8_to_cp1252($s)
    {
        $s = str_replace(self::$utf8, self::$cp1252, $s);

/**/    if (function_exists('utf8_decode'))
/**/    {
            return utf8_decode($s);
/**/    }
/**/    else
/**/    // @codeCoverageIgnoreStart
/**/    {
            return self::utf8_decode($s);
/**/    }
/**/    // @codeCoverageIgnoreEnd
    }

    static function utf8_encode($s)
    {
        $len = strlen($s);
        $e = $s . $s;

        for ($i = 0, $j = 0; $i < $len; ++$i, ++$j) switch (true)
        {
        case $s[$i] < "\x80": $e[$j] = $s[$i]; break;
        case $s[$i] < "\xC0": $e[$j] = "\xC2"; $e[++$j] = $s[$i]; break;
        default:              $e[$j] = "\xC3"; $e[++$j] = chr(ord($s[$i]) - 64); break;
        }

        return substr($e, 0, $j);
    }

    static function utf8_decode($s)
    {
        $len = strlen($s);

        for ($i = 0, $j = 0; $i < $len; ++$i, ++$j)
        {
            switch ($s[$i] & "\xF0")
            {
            case "\xC0":
            case "\xD0":
                $c = (ord($s[$i] & "\x1F") << 6) | ord($s[++$i] & "\x3F");
                $s[$j] = $c < 256 ? chr($c) : '?';
                break;

            case "\xF0": ++$i;
            case "\xE0":
                $s[$j] = '?';
                $i += 2;
                break;

            default:
                $s[$j] = $s[$i];
            }
        }

        return substr($s, 0, $j);
    }
}
