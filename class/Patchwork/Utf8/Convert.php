<?php /****************** vi: set fenc=utf-8 ts=4 sw=4 et: *****************
 *
 *   Copyright : (C) 2012 Nicolas Grekas. All rights reserved.
 *   Email     : p@tchwork.org
 *   License   : http://www.gnu.org/licenses/lgpl.txt GNU/LGPL
 *
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public
 *   License as published by the Free Software Foundation; either
 *   version 3 of the License, or (at your option) any later version.
 *
 ***************************************************************************/

namespace Patchwork\Utf8;

/**
 * UTF-8 encoding converter.
 *
 * XXX Work in progress, has known bugs and interface will change.
 */
class Convert
{
    // UTF-8 to Code Page conversion using best fit mappings
    // See http://www.unicode.org/Public/MAPPINGS/VENDORS/MICSFT/WindowsBestFit/

    static function bestFit($cp, $s, $placeholder = '?')
    {
        if (!$i = strlen($s)) return 0 === $i ? '' : false;

        static $map = array();
        static $ulen_mask = array("\xC0" => 2, "\xD0" => 2, "\xE0" => 3, "\xF0" => 4);

        $cp = (string) (int) $cp;
        $result = '9' === $cp[0] ? $s . $s : $s;

        if ('932' === $cp && 2 === func_num_args()) $placeholder = "\x81\x45"; // Katakana Middle Dot in CP932

        if (!isset($map[$cp]))
        {
            $i = self::getData('bestfit' . $cp);
            if (false === $i) return false;
            $map[$cp] = $i;
        }

        $i = $j = 0;
        $len = strlen($s);
        $cp = $map[$cp];

        while ($i < $len)
        {
            if ($s[$i] < "\x80") $uchr = $s[$i++];
            else
            {
                $ulen = $ulen_mask[$s[$i] & "\xF0"];
                $uchr = substr($s, $i, $ulen);
                $i += $ulen;
            }

            if (isset($cp[$uchr])) $uchr = $cp[$uchr];
            else $uchr = $placeholder;

            isset($uchr[0]) && $result[$j++] = $uchr[0];
            isset($uchr[1]) && $result[$j++] = $uchr[1];
        }

        return substr($result, 0, $j);
    }

    protected static function getData($file)
    {
        $file = __DIR__ . '/data/' . $file . '.ser';
        if (file_exists($file)) return unserialize(file_get_contents($file));
        else return false;
    }
}
