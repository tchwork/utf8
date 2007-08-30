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


/*
 * Partial mbstring implementation in pure PHP
 *
 * Supported through iconv:

mb_convert_encoding           - Convert character encoding
mb_decode_mimeheader          - Decode string in MIME header field
mb_encode_mimeheader          - Encode string for MIME header


 * Not implemented:

mb_check_encoding             - Check if the string is valid for the specified encoding
mb_convert_kana               - Convert "kana" one from another ("zen-kaku", "han-kaku" and more)
mb_convert_variables          - Convert character code in variable(s)
mb_decode_numericentity       - Decode HTML numeric string reference to character
mb_detect_encoding            - Detect character encoding
mb_detect_order               - Set/Get character encoding detection order
mb_encode_numericentity       - Encode character to HTML numeric string reference
mb_ereg_match                 - Regular expression match for multibyte string
mb_ereg_replace               - Replace regular expression with multibyte support
mb_ereg_search_getpos         - Returns start point for next regular expression match
mb_ereg_search_getregs        - Retrieve the result from the last multibyte regular expression match
mb_ereg_search_init           - Setup string and regular expression for multibyte regular expression match
mb_ereg_search_pos            - Return position and length of matched part of multibyte regular expression for predefined multibyte string
mb_ereg_search_regs           - Returns the matched part of multibyte regular expression
mb_ereg_search_setpos         - Set start point of next regular expression match
mb_ereg_search                - Multibyte regular expression match for predefined multibyte string
mb_ereg                       - Regular expression match with multibyte support
mb_eregi_replace              - Replace regular expression with multibyte support ignoring case
mb_eregi                      - Regular expression match ignoring case with multibyte support
mb_get_info                   - Get internal settings of mbstring
mb_http_input                 - Detect HTTP input character encoding
mb_http_output                - Set/Get HTTP output character encoding
mb_language                   - Set/Get current language
mb_list_encodings_alias_names - Returns an array of all supported alias encodings
mb_list_mime_names            - Returns an array or string of all supported mime names
mb_output_handler             - Callback function converts character encoding in output buffer
mb_preferred_mime_name        - Get MIME charset string
mb_regex_encoding             - Returns current encoding for multibyte regex as string
mb_regex_set_options          - Set/Get the default options for mbregex functions
mb_send_mail                  - Send encoded mail
mb_split                      - Split multibyte string using regular expression
mb_strcut                     - Get part of string
mb_strimwidth                 - Get truncated string with specified width
mb_strwidth                   - Return width of string

 */

class utf8_mbstring_500
{
	static function convert_encoding($s, $to_encoding, $from_encoding = null)
	{
		if (function_exists('iconv')) return iconv($from_encoding ? $from_encoding : 'UTF-8', $to_encoding . '//IGNORE', $s);
		trigger_error('mb_convert_encoding() not supported without mbstring or iconv');
		return $s;
	}

	static function decode_mimeheader($s)
	{
		if (function_exists('iconv_mime_decode')) return iconv_mime_decode($s);
		trigger_error('mb_decode_mimeheader() not supported without mbstring or iconv');
		return $s;
	}

	static function encode_mimeheader($s, $charset = null, $transfer_encoding = null, $linefeed = null, $indent = null)
	{
		if (function_exists('iconv_mime_encode')) return iconv_mime_encode('', $s, array(
			'scheme' => null === $transfer_encoding ? 'B' : $transfer_encoding,
			'input-charset' => $charset ? $charset : 'UTF-8',
			'output-charset' => $charset ? $charset : 'UTF-8',
			'line-length' => 76,
			'line-break-chars' => null === $linefeed ? "\r\n" : $linefeed,
		));
		trigger_error('mb_encode_mimeheader() not supported without mbstring or iconv');
		return $s;
	}


	static function convert_case($s, $mode, $encoding = null)
	{
		if ('' === $s) return '';

		switch ($mode)
		{
		case MB_CASE_TITLE:
			self::$encoding = $encoding;
			return preg_replace_callback('/\b\p{Ll}/u', array(__CLASS__, 'title_case_callback'), $s);

		case MB_CASE_UPPER:
			static $upper;
			isset($upper) || $upper = self::loadCaseTable(1);
			$map =& $upper;
			break;

		case MB_CASE_LOWER:
		default:
			static $lower;
			isset($lower) || $lower = self::loadCaseTable(0);
			$map =& $lower;
		}

		static $utf_len_mask = array("\xC0" => 2, "\xD0" => 2, "\xE0" => 3, "\xF0" => 4);

		$i = 0;
		$len = strlen($s);

		while ($i < $len)
		{
			$utf_len = $s[$i] < "\x80" ? 1 : $utf_len_mask[$s[$i] & "\xF0"];
			$utf_chr = substr($s, $i, $utf_len);
			$i += $utf_len;

			if (isset($map[$utf_chr]))
			{
				$utf_chr = $map[$utf_chr];
				$new_len = strlen($utf_chr);

				if ($new_len == $utf_len)
				{
					$new_len = $i;
					do $s[--$new_len] = $utf_chr[--$utf_len];
					while ($utf_len);
				}
				else
				{
					$s = substr_replace($s, $utf_chr, $i, $utf_len);
					$len += $new_len - $utf_len;
					$i   += $new_len - $utf_len;
				}
			}
		}

		return $s;
	}

	static function internal_encoding($encoding = null)
	{
		return null !== $encoding ? preg_match('/^utf-?8$/iD', $encoding) : 'UTF-8';
	}

	static function list_encodings()
	{
		return array('UTF-8');
	}

	static function strlen($s, $encoding = null)
	{
		return strlen(utf8_decode($s));
	}

	static function strlen2($s, $encoding = null)
	{
		// Quickest alternative if utf8_decode() is not available:
		preg_replace('/./u', '', $s, -1, $s);
		return $s;
	}

	static function strpos($haystack, $needle, $offset = 0, $encoding = null)
	{
		if ($offset = (int) $offset) $haystack = self::substr($haystack, $offset);
		$pos = strpos($haystack, $needle);
		return false === $pos ? false : ($offset + ($pos ? self::strlen(substr($haystack, 0, $pos)) : 0));
	}

	static function strtolower($s, $encoding = null)
	{
		return self::convert_case($s, MB_CASE_LOWER, $encoding);
	}

	static function strtoupper($s, $encoding = null)
	{
		return self::convert_case($s, MB_CASE_UPPER, $encoding);
	}

	static function substitute_character($c = null)
	{
		return null !== $c ? false : 'none';
	}

	static function substr($s, $start, $length = null, $encoding = null)
	{
		$slen = self::strlen($s);
		$start = (int) $start;

		if (0 > $start) $start += $slen;
		if (0 > $start) $start = 0;
		if ($start >= $slen) return '';

		$rx = $slen - $start;

		if (null === $length) $length  = $rx;
		else if (0 > $length) $length += $rx;
		if (0 >= $length) return '';

		if ($length > $slen - $start) $length = $rx;

		$rx = '/^' . ($start ? self::preg_offset($start) : '') . '(' . self::preg_offset($length) . ')/u';

		return preg_match($rx, $s, $s) ? $s[1] : '';
	}

	protected static function preg_offset($offset)
	{
		$rx = array();
		$offset = (int) $offset;

		while ($offset > 65535)
		{
			$rx[] = '.{65535}';
			$offset -= 65535;
		}

		return implode('', $rx) . '.{' . $offset . '}';
	}

	protected static function loadCaseTable($upper)
	{
		return unserialize(file_get_contents(
			$upper
				? resolvePath('data/utf8/upperCase.ser')
				: resolvePath('data/utf8/lowerCase.ser')
		));
	}

	protected static $encoding;
	protected static function title_case_callback($s)
	{
		$s = self::convert_case($s[0], MB_CASE_UPPER, self::$encoding);

		$len = strlen($s);
		for ($i = 1; $i < $len && $s[$i] < "\x80"; ++$i) $s[$i] = strtolower($s[$i]);

		return $s;
	}
}
