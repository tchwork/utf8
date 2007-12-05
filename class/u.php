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


/* UTF-8 Grapheme Cluster aware string manipulations.
 *
 * See also:
 * - http://phputf8.sf.net/ and its "see also" section
 * - http://annevankesteren.nl/2005/05/unicode
 * - http://www.unicode.org/reports/tr29/
 *
 */


class u
{
	static function isUTF8($s)
	{
		return @iconv('UTF-8', 'UTF-8', $s) === (string) $s;
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


	// Here is the complete set of native PHP string functions that need UTF-8 awareness

	static function strlen($s)
	{
		$s = self::getGraphemeClusterArray($s);
		return count($s);
	}

	static function strtolower($s) {return mb_strtolower($s, 'UTF-8');}
	static function strtoupper($s) {return mb_strtoupper($s, 'UTF-8');}

	static function substr($s, $start, $len = INF)
	{
		$s = self::getGraphemeClusterArray($s);
		$s = array_slice($s, $start, INF === $len ? PHP_INT_MAX : $len);
		return implode('', $s);
	}

	static function strpos($s, $needle, $offset = 0)
	{
		if ('' !== (string) $s)
		{
			$needle = mb_strpos($s, $needle, $offset, 'UTF-8');
			return $needle ? self::strlen(mb_substr($s, 0, $needle)) : $needle;
		}
		else return false;
	}

	static function stripos($s, $needle, $offset = 0)
	{
		if ('' !== (string) $s)
		{
			$needle = mb_stripos($s, $needle, $offset, 'UTF-8');
			return $needle ? self::strlen(mb_substr($s, 0, $needle)) : $needle;
		}
		else return false;
	}

	static function strrpos($s, $needle, $offset = 0)
	{
		if ('' !== (string) $s)
		{
			$needle = mb_strrpos($s, $needle, $offset, 'UTF-8');
			return $needle ? self::strlen(mb_substr($s, 0, $needle)) : $needle;
		}
		else return false;
	}

	static function strripos($s, $needle, $offset = 0)
	{
		if ('' !== (string) $s)
		{
			$needle = mb_strripos($s, $needle, $offset, 'UTF-8');
			return $needle ? self::strlen(mb_substr($s, 0, $needle)) : $needle;
		}
		else return false;
	}

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
		$s = self::getGraphemeClusterArray($s);
		$s = array_count_values($s);
		return 1 == $mode ? $s[0] : implode('', $s[0]);
	}

	static function ltrim($s, $charlist = INF)
	{
		$charlist = INF === $charlist ? '\s' : preg_quote($charlist, '/');
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

	static function rtrim($s, $charlist = INF)
	{
		$charlist = INF === $charlist ? '\s' : preg_quote($charlist, '/');
		return preg_replace("/[{$charlist}]+$/u", '', $s);
	}

	static function trim($s, $charlist = INF) {return self::rtrim(self::ltrim($s, $charlist), $charlist);}

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
		$s = self::getGraphemeClusterArray($s);
		shuffle($s);
		return implode('', $s);
	}

	static function str_split($s, $len = 1)
	{
		$len = (int) $len;
		if ($len < 1) return str_split($s, $len);

		$s = self::getGraphemeClusterArray($s);
		if (1 === $len) return $s;

		$a = array();
		$j = -1;

		foreach ($s as $i => $s)
		{
			if ($i % $len) $a[$j] .= $s;
			else $a[++$j] = $s;
		}

		return $a;
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

	static function strcspn($s, $mask, $start = INF, $len = INF)
	{
		if ('' === (string) $mask) return null;
		if (INF !== $start || INF !== $len) $s = self::substr($s, $start, $len);
		return preg_match('/^[^' . preg_quote($mask, '/') . ']+/u', $s, $s) ? self::strlen($s[0]) : 0;
	}

	static function strpbrk($s, $charlist)
	{
		return preg_match('/[' . preg_quote($charlist, '/') . '].*/us', $s, $s) ? $s[0] : false;
	}

	static function strrev($s)
	{
		$s = self::getGraphemeClusterArray($s);
		return implode('', array_reverse($s));
	}

	static function strspn($s, $mask, $start = INF, $len = INF)
	{
		if (INF !== $start || INF !== $len) $s = self::substr($s, $start, $len);
		return preg_match('/^['  . preg_quote($mask, '/') . ']+/u', $s, $s) ? self::strlen($s[0]) : 0;
	}

	static function strtr($s, $from, $to = INF)
	{
		if (INF !== $to)
		{
			$from = self::getGraphemeClusterArray($from);
			$to   = self::getGraphemeClusterArray($to);

			$a = count($from);
			$b = count($to);

			     if ($a > $b) $from = array_slice($from, 0, $b);
			else if ($a < $b) $to   = array_slice($to  , 0, $a);

			$from = array_combine($from, $to);
		}

		return strtr($s, $from);
	}

	static function substr_compare($a, $b, $offset, $len = INF, $i = 0)
	{
		$a = self::substr($a, $offset, $len);
		return $i ? self::strcasecmp($a, $b) : strcmp($a, $b);
	}

	static function substr_count($s, $needle, $offset = 0, $len = INF)
	{
		return substr_count(self::substr($s, $offset, $len), $needle);
	}

	static function substr_replace($s, $replace, $start, $len = INF)
	{
		$s       = self::getGraphemeClusterArray($s);
		$replace = self::getGraphemeClusterArray($replace);

		if (INF === $len) $len = count($s);

		array_splice($s, $start, $len, $replace);

		return implode('', $s);
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


	// (CRLF|([ZWNJ-ZWJ]|T+|L*(LV?V+|LV|LVT)T*|L+|[^Control])[Extend]*|[Control])
	const GRAPHEME_CLUSTER_RX = '/(?:\r\n|(?:[\x{200C}\x{200D}]|[ᆨ-ᇹ]+|[ᄀ-ᅟ]*(?:[가개갸걔거게겨계고과괘괴교구궈궤귀규그긔기까깨꺄꺠꺼께껴꼐꼬꽈꽤꾀꾜꾸꿔꿰뀌뀨끄끠끼나내냐냬너네녀녜노놔놰뇌뇨누눠눼뉘뉴느늬니다대댜댸더데뎌뎨도돠돼되됴두둬뒈뒤듀드듸디따때땨떄떠떼뗘뗴또똬뙈뙤뚀뚜뚸뛔뛰뜌뜨띄띠라래랴럐러레려례로롸뢔뢰료루뤄뤠뤼류르릐리마매먀먜머메며몌모뫄뫠뫼묘무뭐뭬뮈뮤므믜미바배뱌뱨버베벼볘보봐봬뵈뵤부붜붸뷔뷰브븨비빠빼뺘뺴뻐뻬뼈뼤뽀뽜뽸뾔뾰뿌뿨쀄쀠쀼쁘쁴삐사새샤섀서세셔셰소솨쇄쇠쇼수숴쉐쉬슈스싀시싸쌔쌰썌써쎄쎠쎼쏘쏴쐐쐬쑈쑤쒀쒜쒸쓔쓰씌씨아애야얘어에여예오와왜외요우워웨위유으의이자재쟈쟤저제져졔조좌좨죄죠주줘줴쥐쥬즈즤지짜째쨔쨰쩌쩨쪄쪠쪼쫘쫴쬐쬬쭈쭤쮀쮜쮸쯔쯰찌차채챠챼처체쳐쳬초촤쵀최쵸추춰췌취츄츠츼치카캐캬컈커케켜켸코콰쾌쾨쿄쿠쿼퀘퀴큐크킈키타태탸턔터테텨톄토톼퇘퇴툐투퉈퉤튀튜트틔티파패퍄퍠퍼페펴폐포퐈퐤푀표푸풔풰퓌퓨프픠피하해햐햬허헤혀혜호화홰회효후훠훼휘휴흐희히]?[ᅠ-ᆢ]+|[가-힣])[ᆨ-ᇹ]*|[ᄀ-ᅟ]+|[^\p{Cc}\p{Cf}\p{Zl}\p{Zp}])[\p{Mn}\p{Me}\x{09BE}\x{09D7}\x{0B3E}\x{0B57}\x{0BBE}\x{0BD7}\x{0CC2}\x{0CD5}\x{0CD6}\x{0D3E}\x{0D57}\x{0DCF}\x{0DDF}\x{200C}\x{200D}\x{1D165}\x{1D16E}-\x{1D172}]*|[\p{Cc}\p{Cf}\p{Zl}\p{Zp}])/u';

	static function getGraphemeClusterArray($s)
	{
		preg_match_all(self::GRAPHEME_CLUSTER_RX, $s, $s);
		return $s[0];
	}
}
