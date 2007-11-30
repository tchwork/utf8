<?php /*********************************************************************
 *
 *   Copyright : (C) 2006 Nicolas Grekas. All rights reserved.
 *   Email     : p@tchwork.org
 *   License   : http://www.gnu.org/licenses/lgpl.txt GNU/LGPL
 *
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public
 *   License as published by the Free Software Foundation; either
 *   version 3 of the License, or (at your option) any later version.
 *
 ***************************************************************************/


/* UTF-8 aware string manipulations.
 *
 * TODO:
 * - handle grapheme clusters & co. (http://unicode.org/reports/tr29/)
 * - add support for security mechanisms (http://unicode.org/reports/tr39/)
 *
 * See also:
 * - http://phputf8.sf.net/ and its "see also" section
 * - http://annevankesteren.nl/2005/05/unicode

 */


class u
{
	static function isUTF8($s)
	{
		return $s === @iconv('UTF-8', 'UTF-8', $s);
	}


	// Unicode Normalization functions.
	// Input strings have to be valid UTF-8.

	static function toNFC($s)  {return utf8_normalizer::toNFC($s);}
	static function toNFD($s)  {return utf8_normalizer::toNFD($s);}
	static function toNFKC($s) {return utf8_normalizer::toNFC($s, true);}
	static function toNFKD($s) {return utf8_normalizer::toNFD($s, true);}
	static function removeAccents($s) {return utf8_normalizer::removeAccents($s);}


	// Unicode transformation for caseless matching
	// see http://unicode.org/reports/tr21/tr21-5.html

	protected static $commonCaseFold  = array(
		'µ' => 'μ', 'ſ' => 's', "\xcd\x85" => 'ι', 'ς' => 'σ',
		"\xcf\x90" => 'β', "\xcf\x91" => 'θ', "\xcf\x95" => 'φ',
		"\xcf\x96" => 'π', "\xcf\xb0" => 'κ', "\xcf\xb1" => 'ρ',
		"\xcf\xb5" => 'ε', "\xe1\xba\x9b" => "\xe1\xb9\xa1",
		"\xe1\xbe\xbe" => 'ι',
	);

	static function strtocasefold($s, $full = true, $turkish = false)
	{
		$s = strtr($s, self::$commonCaseFold);

		if ($turkish)
		{
			false !== strpos($s, 'I') && $s = str_replace('I', 'ı', $s);
			$full && false !== strpos($s, 'İ') && $s = str_replace('İ', 'i', $s);
		}

		if ($full)
		{
			static $fullCaseFold = false;
			$fullCaseFold || $fullCaseFold = unserialize(file_get_contents(resolvePath('data/utf8/caseFold_full.txt')));

			$s = strtr($s, $fullCaseFold);
		}

		return self::strtolower($s);
	}


	// Here is the complete set of native PHP string functions that need UTF-8 awareness,
	// Input strings should be in Normalization Form C, Canonical Composition.

	static function strlen($s)     {return mb_strlen($s, 'UTF-8');}
	static function strtolower($s) {return mb_strtolower($s, 'UTF-8');}
	static function strtoupper($s) {return mb_strtoupper($s, 'UTF-8');}
	static function substr  ($s, $start, $len = null) {return mb_substr($s, $start, $len, 'UTF-8');}
	static function strpos  ($s, $needle, $offset = 0) {return ''===$s ? false : mb_strpos  ($s, $needle, $offset, 'UTF-8');}
	static function strrpos ($s, $needle, $offset = 0) {return ''===$s ? false : mb_strrpos ($s, $needle, $offset, 'UTF-8');}
	static function stripos ($s, $needle, $offset = 0) {return mb_stripos ($s, $needle, $offset, 'UTF-8');}
	static function strripos($s, $needle, $offset = 0) {return mb_strripos($s, $needle, $offset, 'UTF-8');}
	static function stristr ($s, $needle) {return mb_stristr ($s, $needle, false, 'UTF-8');}
	static function strrchr ($s, $needle) {return mb_strrchr ($s, $needle, false, 'UTF-8');}
	static function strrichr($s, $needle) {return mb_strrichr($s, $needle, false, 'UTF-8');}
	static function strstr  ($s, $needle) {return mb_strstr  ($s, $needle, false, 'UTF-8');}
	static function htmlentities    ($s, $quote_style = ENT_COMPAT) {return htmlentities    ($s, $quote_style, 'UTF-8');}
	static function htmlspecialchars($s, $quote_style = ENT_COMPAT) {return htmlspecialchars($s, $quote_style, 'UTF-8');}
	static function wordwrap($s, $width = 75, $break = "\n", $cut = false) {return pipe_wordwrap::php($s, $width, $break, $cut);}

	static function chr($c)
	{
		$c %= 0x200000;

		return $c < 0x80    ? chr($c) : (
		       $c < 0x800   ? chr(0xc0 | $c>> 6) . chr(0x80 | $c     & 0x3f) : (
		       $c < 0x10000 ? chr(0xe0 | $c>>12) . chr(0x80 | $c>> 6 & 0x3f) . chr(0x80 | $c    & 0x3f) : (
		                      chr(0xf0 | $c>>18) . chr(0x80 | $c>>12 & 0x3f) . chr(0x80 | $c>>6 & 0x3f) . chr(0x80 | $c & 0x3f)
		)));
	}

	static function count_chars($s, $mode = 1)
	{
		if (1 != $mode && 3 != $mode) trigger_error('u::count_chars(): allowed $mode are 1 or 3', E_USER_ERROR);
		preg_match_all('/./us', $s, $s);
		$s = array_count_values($s[0]);
		return 1 == $mode ? $s[0] : implode('', $s[0]);
	}

	static function ltrim($s, $charlist = null)
	{
		$charlist = null === $charlist ? '\s' : preg_quote($charlist, '/');
		return preg_replace("/^[{$charlist}]+/u", '', $s);
	}

	static function ord($s)
	{
		$s = unpack('C*', substr($s, 0, 6));
		$a = $s ? $s[1] : 0;

		return 240 <= $a && $a <= 255 ? (($a-240) << 18) + (($s[2]-128) << 12) + (($s[3]-128) << 6) + $s[4]-128 : (
		       224 <= $a && $a <= 239 ? (($a-224) << 12) + (($s[2]-128) <<  6) +   $s[3]-128 : (
		       192 <= $a && $a <= 223 ? (($a-192) <<  6) +   $s[2]-128 : (
		       $a)));
	}

	static function rtrim($s, $charlist = null)
	{
		$charlist = null === $charlist ? '\s' : preg_quote($charlist, '/');
		return preg_replace("/[{$charlist}]+$/u", '', $s);
	}

	static function trim($s, $charlist = null) {return self::rtrim(self::ltrim($s, $charlist), $charlist);}

	static function html_entity_decode($s, $quote_style = ENT_COMPAT)
	{
		$s = strtr($s, array(
			'&QUOT;' => '&quot;', '&LT;' => '&lt;', '&AMP;' => '&amp;', '&TRADE;' => '&trade;',
			'&COPY;' => '&copy;', '&GT;' => '&gt;', '&REG;' => '&reg;', '&apos;'  => '&#39;'
		));

		return html_entity_decode($s, $quote_style, 'UTF-8');
	}

	static function get_html_translation_table($table = HTML_SPECIALCHARS, $quote_style = ENT_COMPAT)
	{
		if (HTML_ENTITIES == $table)
		{
			static $entities = array();
			$entities || $entities = unserialize(file_get_contents(resolvePath('data/utf8/htmlentities.ser')));
			return $entities + get_html_translation_table(HTML_SPECIALCHARS, $quote_style);
		}
		else return get_html_translation_table($table, $quote_style);
	}

	static function str_ireplace($search, $replace, $subject, &$count = null)
	{
		return preg_replace('/' . preg_quote($search, '/') . '/ui', $replace, $subject, -1, $count);
	}

	static function str_pad($s, $len, $pad = ' ', $type = STR_PAD_RIGHT)
	{
		$slen = self::strlen($s);
		if ($len <= $slen) return $s;

		$padlen = self::strlen($pad);
		$freelen = $len - $slen;
		$len = $freelen % $padlen;

		if (STR_PAD_RIGHT == $type) return $s . str_repeat($pad, $freelen / $padlen) . ($len ? self::substr($pad, 0, $len) : '');
		if (STR_PAD_LEFT  == $type) return      str_repeat($pad, $freelen / $padlen) . ($len ? self::substr($pad, 0, $len) : '') . $s;

		if (STR_PAD_BOTH == $type)
		{
			$freelen /= 2;

			$type = ceil($freelen);
			$len = $type % $padlen;
			$s  .=  str_repeat($pad, $type / $padlen) . ($len ? self::substr($pad, 0, $len) : '');

			$type = floor($freelen);
			$len = $type % $padlen;
			return  str_repeat($pad, $type / $padlen) . ($len ? self::substr($pad, 0, $len) : '') . $s;
		}

		trigger_error('u::str_pad(): Padding type has to be STR_PAD_LEFT, STR_PAD_RIGHT, or STR_PAD_BOTH.');
	}

	static function str_shuffle($s)
	{
		preg_match_all('/./us', $s, $s);
		shuffle($s[0]);
		return implode('', $s[0]);
	}

	static function str_split($s, $len = 1)
	{
		$len = (int) $len;

		if ($len < 1) return false;
		if (self::strlen($s) <= $len) return array(&$s);

		preg_match_all('/.{' . $len . '}|.+?$/us', $s, $s);

		return $s[0];
	}

	static function str_word_count($s, $format = 0, $charlist = '')
	{
		$charlist = '[\pL' . preg_quote($charlist, '/') . ']';
		$s = preg_split("/({$charlist}+(?:[\pPd’']{$charlist}+)*)/u", $s, -1, PREG_SPLIT_DELIM_CAPTURE);

		$charlist = array();
		$len = count($s);

		if (1 == $format) for ($i = 1; $i < $len; $i+=2) $charlist[] = $s[$i];
		else if (2 == $format)
		{
			$offset = self::strlen($s[0]);
			for ($i = 1; $i < $len; $i+=2)
			{
				$charlist[$offset] = $s[$i];
				$offset += self::strlen($s[$i]) + self::strlen($s[$i+1]);
			}
		}
		else $charlist = ($len - 1) / 2;

		return $charlist;
	}

	static function strcasecmp   ($a, $b) {return strcmp   (self::strtocasefold($a, true), self::strtocasefold($b, true));}
	static function strnatcasecmp($a, $b) {return strnatcmp(self::strtocasefold($a, true), self::strtocasefold($b, true));}
	static function strncasecmp  ($a, $b, $len) {return self::strncmp(self::strtocasefold($a), self::strtocasefold($b), $len);}
	static function strncmp      ($a, $b, $len) {return strcmp(self::substr($a, 0, $len), self::substr($b, 0, $len));}

	static function strcspn($s, $mask, $start = null, $len = null)
	{
		if ('' === (string) $mask) return null;
		if (null !== $start || null !== $len) $s = self::substr($s, $start, $len);
		return preg_match('/^[^' . preg_quote($mask) . ']+/u', $s, $s) ? self::strlen($s[0]) : 0;
	}

	static function strpbrk($s, $charlist)
	{
		return preg_match('/[' . preg_quote($charlist, '/') . '].*/us', $s, $s) ? $s[0] : false;
	}

	static function strrev($s)
	{
		preg_match_all('/./us', $s, $s);
		return implode('', array_reverse($s[0]));
	}

	static function strspn($s, $mask, $start = null, $len = null)
	{
		if (null !== $start || null !== $len) $s = self::substr($s, $start, $len);
		return preg_match('/^['  . preg_quote($mask) . ']+/u', $s, $s) ? self::strlen($s[0]) : 0;
	}

	static function strtr($s, $from, $to = null)
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

		return strtr($s, $from);
	}

	static function substr_compare($a, $b, $offset, $len = null, $i = 0)
	{
		$a = self::substr($offset, $len);
		return $i ? self::strcasecmp($a, $b) : strcmp($a, $b);
	}

	static function substr_count($s, $needle, $offset = 0, $len = null)
	{
		return substr_count(self::substr($s, $offset, $len), $needle);
	}

	static function substr_replace($s, $replace, $start, $len = null)
	{
		preg_match_all('/./us', $s      , $s);
		preg_match_all('/./us', $replace, $replace);

		if (null === $len) $len = count($s[0]);

		array_splice($s[0], $start, $len, $replace[0]);

		return implode('', $s[0]);
	}

	static function ucfirst($s)
	{
		$c = mb_substr($s, 0, 1);
		return self::ucwords($c) . substr($s, strlen($c));
	}

	static function ucwords($s)
	{
		return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
	}
}
