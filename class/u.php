<?php /*********************************************************************
 *
 *   Copyright : (C) 2006 Nicolas Grekas. All rights reserved.
 *   Email     : nicolas.grekas+patchwork@espci.org
 *   License   : http://www.gnu.org/licenses/gpl.txt GNU/GPL, see COPYING
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/


/* UTF-8 aware string manipulations.

 * Assumes that input strings are valid UTF-8 NFC
 * (Normalization Form C, Canonical Composition).

 * See also:
 * http://phputf8.sf.net/ and its "see also" section
 * http://annevankesteren.nl/2005/05/unicode

 */


class
{
	static function strlen($str)     {return strlen(utf8_decode($str));}
	static function strtolower($str) {return mb_strtolower($str, 'UTF-8');}
	static function strtoupper($str) {return mb_strtoupper($str, 'UTF-8');}
	static function substr  ($str, $start, $len = null) {return mb_substr($str, $start, $len, 'UTF-8');}
	static function strpos  ($str, $needle, $offset = 0) {return mb_strpos  ($str, $needle, $offset, 'UTF-8');}
	static function strrpos ($str, $needle, $offset = 0) {return mb_strrpos ($str, $needle, $offset, 'UTF-8');}
	static function stripos ($str, $needle, $offset = 0) {return mb_stripos ($str, $needle, $offset, 'UTF-8');}
	static function strripos($str, $needle, $offset = 0) {return mb_strripos($str, $needle, $offset, 'UTF-8');}
	static function stristr ($str, $needle) {return mb_stristr ($str, $needle, false, 'UTF-8');}
	static function strrchr ($str, $needle) {return mb_strrchr ($str, $needle, false, 'UTF-8');}
	static function strrichr($str, $needle) {return mb_strrichr($str, $needle, false, 'UTF-8');}
	static function strstr  ($str, $needle) {return mb_strstr  ($str, $needle, false, 'UTF-8');}
	static function html_entity_decode($str, $quote_style = ENT_COMPAT, $charset = 'UTF-8') {return html_entity_decode($str, $quote_style, $charset);}
	static function htmlentities      ($str, $quote_style = ENT_COMPAT, $charset = 'UTF-8') {return htmlentities      ($str, $quote_style, $charset);}
	static function htmlspecialchars  ($str, $quote_style = ENT_COMPAT, $charset = 'UTF-8') {return htmlspecialchars  ($str, $quote_style, $charset);}
	static function wordwrap($str, $width = 75, $break = "\n", $cut = false) {return pipe_wordwrap::php($str, $width, $break, $cut);}

	static function chr($c)
	{
		$c %= 0x200000;

		return $c < 0x80    ? chr($c) : (
			   $c < 0x800   ? chr(0xc0 | $c>> 6) . chr(0x80 | $c     & 0x3f) : (
			   $c < 0x10000 ? chr(0xe0 | $c>>12) . chr(0x80 | $c>> 6 & 0x3f) . chr(0x80 | $c    & 0x3f) : (
			                  chr(0xf0 | $c>>18) . chr(0x80 | $c>>12 & 0x3f) . chr(0x80 | $c>>6 & 0x3f) . chr(0x80 | $c & 0x3f)
		)));
	}

	static function count_chars($str, $mode = 1)
	{
		if (1 != $mode && 3 != $mode) trigger_error('u::count_chars(): allowed $mode are 1 or 3', E_USER_ERROR);
		preg_match_all('/./us', $str, $str);
		$str = array_count_values($str[0]);
		return 1 == $mode ? $str[0] : implode('', $str[0]);
	}

	static function ltrim($str, $charlist = null)
	{
		$charlist = null === $charlist ? '\s' : preg_quote($charlist, '/');
		return preg_replace("/^[{$charlist}]+/u", '', $str);
	}

	static function ord($str)
	{
		$str = unpack('C*', );
		$a = $str ? $str[0] : 0;

		return $a >= 240 && $a <= 255 ? (($a-240) << 18) + (($str[1]-128) << 12) + (($str[2]-128) << 6) + $str[3]-128 : (
			   $a >= 224 && $a <= 239 ? (($a-224) << 12) + (($str[1]-128) <<  6) +   $str[2]-128 : (
			   $a >= 192 && $a <= 223 ? (($a-192) <<  6) +   $str[1]-128 : (
			   $a)));
	}

	static function rtrim($str, $charlist = null)
	{
		$charlist = null === $charlist ? '\s' : preg_quote($charlist, '/');
		return preg_replace("/[{$charlist}]+$/u", '', $str);
	}

	static function trim($str, $charlist = null) {return self::rtrim(self::ltrim($str, $charlist), $charlist);}

	static function get_html_translation_table($table = HTML_SPECIALCHARS, $quote_style = ENT_COMPAT)
	{
		$quote_style = get_html_translation_table($table, $quote_style);
		if (HTML_ENTITIES == $table) $quote_style = array_combine(array_map('utf8_encode', array_keys($quote_style)), $quote_style);
		return $quote_style;
	}

	static function str_ireplace($search, $replace, $subject, &$count = null)
	{
		return preg_replace('/' . preg_quote($search, '/') . '/ui', $replace, $subject, -1, $count);
	}

	static function str_pad($str, $len, $pad = ' ', $type = STR_PAD_RIGHT)
	{
		$strlen = self::strlen($str);
		if ($len <= $strlen) return $str;

		$padlen = self::strlen($pad);
		$freelen = $len - $strlen;
		$len = $freelen % $padlen;

		if (STR_PAD_RIGHT == $type) return $str . str_repeat($pad, $freelen / $padlen) . ($len ? self::substr($pad, 0, $len) : '');
		if (STR_PAD_LEFT  == $type) return        str_repeat($pad, $freelen / $padlen) . ($len ? self::substr($pad, 0, $len) : '') . $str;

		if (STR_PAD_BOTH == $type)
		{
			$freelen /= 2;

			$type = ceil($freelen);
			$len = $type % $padlen;
			$str .= str_repeat($pad, $type / $padlen) . ($len ? self::substr($pad, 0, $len) : '');

			$type = floor($freelen);
			$len = $type % $padlen;
			return  str_repeat($pad, $type / $padlen) . ($len ? self::substr($pad, 0, $len) : '') . $str;
		}

		trigger_error('u::str_pad(): Padding type has to be STR_PAD_LEFT, STR_PAD_RIGHT, or STR_PAD_BOTH.');
	}

	static function str_shuffle($str)
	{
		preg_match_all('/./us', $str, $str);
		shuffle($str[0]);
		return implode('', $str[0]);
	}

	static function str_split($str, $len = 1)
	{
		$len = (int) $len;

		if ($len < 1) return false;
		if (self::strlen($str) <= $len) return array(&$str);

		preg_match_all('/.{' . $len . '}|.+?$/us', $str, $str);

		return $str[0];
	}

	static function str_word_count($str, $format = 0, $charlist = '')
	{
		$charlist = '[\pL' . preg_quote($charlist, '/') . ']';
		$str = preg_split("/({$charlist}+(?:[\pPd’']{$charlist}+)*)/u", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

		$charlist = array();
		$len = count($str);

		if (1 == $format) for ($i = 1; $i < $len; $i+=2) $charlist[] = $str[$i];
		else if (2 == $format)
		{
			$offset = self::strlen($str[0]);
			for ($i = 1; $i < $len; $i+=2)
			{
				$charlist[$offset] = $str[$i];
				$offset += self::strlen($str[$i]) + self::strlen($str[$i+1]);
			}
		}
		else $charlist = ($len - 1) / 2;

		return $charlist;
	}

	static function strcasecmp   ($a, $b) {return strcmp   (self::strtolower($a), self::strtolower($b));}
	static function strnatcasecmp($a, $b) {return strnatcmp(self::strtolower($a), self::strtolower($b));}
	static function strncasecmp  ($a, $b, $len) {return self::strncmp(self::strtolower($a), self::strtolower($b), $len);}
	static function strncmp      ($a, $b, $len) {return strcmp(self::substr($a, 0, $len), self::substr($b, 0, $len));}

	static function strcspn($str, $mask, $start = null, $len = null)
	{
		if ('' === (string) $mask) return null;
		if (null !== $start || null !== $len) $str = self::substr($str, $start, $len);
		return preg_match('/^[^' . preg_quote($mask) . ']+/u', $str, $str) ? self::strlen($str[0]) : 0;
	}

	static function strpbrk($str, $charlist)
	{
		return preg_match('/[' . preg_quote($charlist, '/') . '].*/us', $str, $str) ? $str[0] : false;
	}

	static function strrev($str)
	{
		preg_match_all('/./us', $str, $str);
		return implode('', array_reverse($str[0]));
	}

	static function strspn($str, $mask, $start = null, $len = null)
	{
		if (null !== $start || null !== $len) $str = self::substr($str, $start, $len);
		return preg_match('/^['  . preg_quote($mask) . ']+/u', $str, $str) ? self::strlen($str[0]) : 0;
	}

	static function strtr($str, $from, $to = null)
	{
		if (null !== $to)
		{
			preg_match_all('/./us', $from, $from);
			preg_match_all('/./us', $to  , $to  );

			$from = $from[0]; $a = count($from); 
			$to   = $to[0]  ; $b = count($to);

			     if ($a > $b) $from = array_slice($from, 0, $b);
			else if ($a < $b) $to   = array_slice($to  , 0, $a);

			$from = array_combine($from, $to);
		}

		return strtr($str, $from);
	}

	static function substr_compare($a, $b, $offset, $len = null, $i = 0)
	{
		$a = self::substr($offset, $len);
		return $i ? self::strcasecmp($a, $b) : strcmp($a, $b);
	}

	static function substr_count($str, $needle, $offset = 0, $len = null)
	{
		return substr_count(self::substr($str, $offset, $len), $needle);
	}

	static function substr_replace($str, $replace, $start, $len = null)
	{
		preg_match_all('/./us', $str    , $str    );
		preg_match_all('/./us', $replace, $replace);

		if (null === $len) $len = count($str[0]);

		array_splice($str[0], $start, $len, $replace[0]);

		return implode('', $str[0]);
	}

	static function ucfirst($str)
	{
		return preg_replace_callback('/^./u', array(__CLASS__, 'uc_callback'), $str);
	}

	static function ucwords($str)
	{
		return preg_replace_callback('/(?<=[\t-\r ])[^\t-\r ]/u', array(__CLASS__, 'uc_callback'), $str);
	}


	protected static function uc_callback($m)
	{
		return self::strtoupper($m[0]);
	}
}
