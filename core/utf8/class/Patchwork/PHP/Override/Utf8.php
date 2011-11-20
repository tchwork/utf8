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

/**/$map = array(
/**/    "\x80 \x82 \x83 \x84 \x85 \x86 \x87 \x88 \x89 \x8A \x8B \x8C \x8E \x91 \x92 \x93 \x94 \x95 \x96 \x97 \x98 \x99 \x9A \x9B \x9C \x9E \x9F",
/**/     '€    ‚    ƒ    „    …    †    ‡    ˆ    ‰    Š    ‹    Œ    Ž    ‘    ’    “    ”    •    –    —    ˜    ™    š    ›    œ    ž    Ÿ'
/**/);
/**/
/**/$map[0] = explode('-', "\xC2" . str_replace(' ', "-\xC2", $map[0]));
/**/$map[1] = explode('    ', $map[1]);

Patchwork_PHP_Override_Utf8::$cp1252 = /*<*/$map[0]/*>*/;
Patchwork_PHP_Override_Utf8::$utf8   = /*<*/$map[1]/*>*/;


/**
 * Basic string functions enhanced to Utf-8.
 *
 * htmlspecialchars and htmlentities default charset is set to UTF-8,
 * adding the $double_enc parameter introduced in PHP 5.2.3.
 *
 * utf8_encode/decode are enhanced to Windows-1252.
 */
class Patchwork_PHP_Override_Utf8
{
    static $cp1252, $utf8;

    static function htmlspecialchars($s, $style = ENT_COMPAT, $charset = 'UTF-8', $double_enc = true)
    {
        return $double_enc || false === strpos($s, '&') || false === strpos($s, ';')
            ? htmlspecialchars($s, $style, $charset)
            : htmlspecialchars(html_entity_decode($s, $style, $charset), $style, $charset);
    }

    static function htmlentities($s, $style = ENT_COMPAT, $charset = 'UTF-8', $double_enc = true)
    {
        return $double_enc || false === strpos($s, '&') || false === strpos($s, ';')
            ? htmlentities($s, $style, $charset)
            : htmlentities(html_entity_decode($s, $style, $charset), $quote_style, $charset);
    }

    static function utf8_encode($s)
    {
/**/    if (function_exists('utf8_encode'))
/**/    {
            $s = utf8_encode($s);
/**/    }
/**/    else if (extension_loaded('iconv') && '§' === @iconv('ISO-8859-1', 'UTF-8', "\xA7"))
/**/    {
            $s = iconv('ISO-8859-1', 'UTF-8', $s);
/**/    }
/**/    else
/**/    {
            $len = strlen($s);
            $e = $s . $s;

            for ($i = 0, $j = 0; $i < $len; ++$i, ++$j) switch (true)
            {
            case $s[$i] < "\x80": $e[$j] = $s[$i]; break;
            case $s[$i] < "\xC0": $e[$j] = "\xC2"; $e[++$j] = $s[$i]; break;
            default:              $e[$j] = "\xC3"; $e[++$j] = chr(ord($s[$i]) - 64); break;
            }

            $s = substr($e, 0, $j);
/**/    }

        if (false !== strpos($s, "\xC2"))
        {
            $s = str_replace(self::$cp1252, self::$utf8, $s);
        }

        return $s;
    }

    function utf8_decode($s)
    {
        $s = str_replace(self::$utf8, self::$cp1252, $s);

/**/    if (function_exists('utf8_decode'))
/**/    {
            return utf8_decode($s);
/**/    }
/**/    else
/**/    {
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
/**/    }
    }
}
