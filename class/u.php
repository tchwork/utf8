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
 * http://phputf8.sf.net/
 * http://annevankesteren.nl/2005/05/unicode


 * Not implemented, but could be worth:

chr - Return a specific character
count_chars - Return information about characters used in a string
ord - Return ASCII value of character
str_pad - Pad a string to a certain length with another string
str_word_count - Return information about words used in a string
strpbrk - Search a string for any of a set of characters
strtok - Tokenize string
strtr - Translate certain characters
substr_compare - Binary safe optionally case insensitive comparison of 2 strings from an offset, up to length characters
substr_count - Count the number of substring occurrences

 * Via transliteration ?
metaphone - Calculate the metaphone key of a string
soundex - Calculate the soundex key of a string

 */


class
{
	static function strlen($str)     {return strlen(utf8_decode($str));}
	static function strtolower($str) {return mb_strtolower($str, 'UTF-8');}
	static function strtoupper($str) {return mb_strtoupper($str, 'UTF-8');}
	static function substr  ($str, $start, $length = null) {return mb_substr($str, $start, $length, 'UTF-8');}
	static function strpos  ($str, $needle, $offset = 0) {return mb_strpos  ($str, $needle, $offset, 'UTF-8');}
	static function strrpos ($str, $needle, $offset = 0) {return mb_strrpos ($str, $needle, $offset, 'UTF-8');}
	static function stripos ($str, $needle, $offset = 0) {return mb_stripos ($str, $needle, $offset, 'UTF-8');}
	static function strripos($str, $needle, $offset = 0) {return mb_strripos($str, $needle, $offset, 'UTF-8');}
	static function stristr ($str, $needle) {return mb_stristr ($str, $needle, false, 'UTF-8');}
	static function strrchr ($str, $needle) {return mb_strrchr ($str, $needle, false, 'UTF-8');}
	static function strrichr($str, $needle) {return mb_strrichr($str, $needle, false, 'UTF-8');}
	static function strstr  ($str, $needle) {return mb_strstr  ($str, $needle, false, 'UTF-8');}
	static function strchr  ($str, $needle) {return self::strstr($str, $needle);}
	static function html_entity_decode($str, $quote_style = ENT_COMPAT, $charset = 'UTF-8') {return html_entity_decode($str, $quote_style, $charset);}
	static function htmlentities      ($str, $quote_style = ENT_COMPAT, $charset = 'UTF-8') {return htmlentities      ($str, $quote_style, $charset);}
	static function htmlspecialchars  ($str, $quote_style = ENT_COMPAT, $charset = 'UTF-8') {return htmlspecialchars  ($str, $quote_style, $charset);}
	static function wordwrap($str, $width = 75, $break = "\n", $cut = false) {return pipe_wordwrap::php($str, $width, $break, $cut);}
	static function chop($str, $charlist = null) {return self::rtrim($str, $charlist);}

	static function ltrim($str, $charlist = null)
	{
		$charlist = null === $charlist ? '\s' : preg_quote($charlist, "'");
		return preg_replace("'^[{$charlist}]+'u", '', $str);
	}

	static function rtrim($str, $charlist = null)
	{
		$charlist = null === $charlist ? '\s' : preg_quote($charlist, "'");
		return preg_replace("'[{$charlist}]+$'u", '', $str);
	}

	static function trim($str, $charlist = null) {return self::rtrim(self::ltrim($str, $charlist), $charlist);}

	static function get_html_translation_table($table = HTML_SPECIALCHARS, $quote_style = ENT_COMPAT)
	{
		$quote_style = get_html_translation_table($table, $quote_style);
		if (HTML_ENTITIES == $table)
		{
			$table = array();
			foreach ($quote_style as $k => &$v) $table[ utf8_encode($k) ] =& $v;
			$quote_style =& $table;
		}
		return $quote_style;
	}

	static function str_ireplace($search, $replace, $subject, &$count = null)
	{
		return preg_replace("'" . preg_quote($search, "'") . "'ui", $replace, $subject, -1, $count);
	}

	static function str_shuffle($str)
	{
		preg_match_all('/./us', $str, $str);
		shuffle($str);
		return implode('', $str);
	}

	static function str_split($str, $len = 1)
	{
		$len = (int) $len;

		if ($len < 1) return false;
		if (self::strlen($str) <= $len) return array(&$str);

		preg_match_all('/.{' . $len . '}|.+?$/us', $str, $str);

		return $str[0];
	}

	static function strcasecmp   ($a, $b) {return strcmp   (self::strtolower($a), self::strtolower($b));}
	static function strnatcasecmp($a, $b) {return strnatcmp(self::strtolower($a), self::strtolower($b));}
	static function strncasecmp  ($a, $b, $len) {return self::strncmp(self::strtolower($a), self::strtolower($b), $len);}
	static function strncmp      ($a, $b, $len) {return strcmp(self::substr($a, 0, $len), self::substr($b, 0, $len));}

	static function strrev($str)
	{
		preg_match_all('/./us', $str, $str);
		return implode('', array_reverse($str[0]));
	}

	static function strcspn($str, $mask, $start = null, $len = null)
	{
		if ('' === (string) $mask) return null;
		if (null !== $start || null !== $len) $str = self::substr($str, $start, $len);
		return preg_match('/^[^' . preg_quote($mask) . ']+/u', $str, $str) ? self::strlen($str[0]) : 0;
	}

	static function strspn($str, $mask, $start = null, $len = null)
	{
		if (null !== $start || null !== $len) $str = self::substr($str, $start, $len);
		return preg_match('/^['  . preg_quote($mask) . ']+/u', $str, $str) ? self::strlen($str[0]) : 0;
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
		return preg_replace_callback("'^.'u", array(__CLASS__, 'uc_callback'), $str);
	}

	static function ucwords($str)
	{
		return preg_replace_callback("'(?<=[\t-\r ])[^\t-\r ]'u", array(__CLASS__, 'uc_callback'), $str);
	}


	protected static function uc_callback($m)
	{
		return self::strtoupper($m[0]);
	}
}
