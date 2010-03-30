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


 * Implemented:

mb_convert_encoding     - Convert character encoding
mb_decode_mimeheader    - Decode string in MIME header field
mb_encode_mimeheader    - Encode string for MIME header XXX NATIVE IMPLEMENTATION IS REALLY BUGGED
mb_convert_case         - Perform case folding on a string
mb_internal_encoding    - Set/Get internal character encoding
mb_list_encodings       - Returns an array of all supported encodings
mb_strlen               - Get string length
mb_strpos               - Find position of first occurrence of string in a string
mb_strrpos              - Find position of last occurrence of a string in a string
mb_strtolower           - Make a string lowercase
mb_strtoupper           - Make a string uppercase
mb_substitute_character - Set/Get substitution character
mb_substr               - Get part of string

 */


if (!function_exists('mb_strlen'))
{
	define('MB_OVERLOAD_MAIL',   1);
	define('MB_OVERLOAD_STRING', 2);
	define('MB_OVERLOAD_REGEX',  4);
	define('MB_CASE_UPPER', 0);
	define('MB_CASE_LOWER', 1);
	define('MB_CASE_TITLE', 2);


	function mb_convert_encoding($str, $to_encoding, $from_encoding = INF) {return utf8_mbstring_500::convert_encoding($str, $to_encoding, $from_encoding);}
	function mb_decode_mimeheader($str) {return utf8_mbstring_500::decode_mimeheader($str);}
	function mb_encode_mimeheader($str, $charset = INF, $transfer_encoding = INF, $linefeed = INF, $indent = INF)
	{
		return utf8_mbstring_500::encode_mimeheader($str, $charset, $transfer_encoding, $linefeed, $indent);
	}

	function mb_convert_case($str, $mode, $encoding = INF) {return utf8_mbstring_500::convert_case($str, $mode, $encoding);}
	function mb_internal_encoding($encoding = INF)         {return utf8_mbstring_500::internal_encoding($encoding);}
	function mb_list_encodings()                           {return utf8_mbstring_500::list_encodings();}
	function mb_strlen($str, $encoding = INF)              {return utf8_mbstring_500::strlen($str, $encoding);}
	function mb_strpos ($haystack, $needle, $offset = 0, $encoding = INF)    {return utf8_mbstring_500::strpos ($haystack, $needle, $offset, $encoding);}
	function mb_strrpos($haystack, $needle, $offset = 0, $encoding = INF)    {return utf8_mbstring_520::strrpos($haystack, $needle, $offset, $encoding);}
	function mb_strtolower($str, $encoding = INF)                            {return utf8_mbstring_500::strtolower($str, $encoding);}
	function mb_strtoupper($str, $encoding = INF)                            {return utf8_mbstring_500::strtoupper($str, $encoding);}
	function mb_substitute_character($char = INF)                            {return utf8_mbstring_500::substitute_character($char);}
	function mb_substr($str, $start, $length = PHP_INT_MAX, $encoding = INF) {return utf8_mbstring_500::substr($str, $start, $length, $encoding);}
}


class utf8_mbstring_500
{
	protected static $internal_encoding = 'UTF-8';


	static function convert_encoding($s, $to_encoding, $from_encoding = INF)
	{
		INF === $from_encoding && $from_encoding = self::$internal_encoding;

		if ('base64' === $to_encoding) return 'base64' === $from_encoding ? $s : base64_encode($s);

		if ('base64' === $from_encoding)
		{
			$s = base64_decode($s);
			$from_encoding = $to_encoding;
		}

		if ('html-entities' === $to_encoding)
		{
			'html-entities' === $from_encoding && $from_encoding = 'ISO-8859-1';
			'utf-8' === $from_encoding || $s = iconv($from_encoding, 'UTF-8//IGNORE', $s);
			return preg_replace_callback('/[\x80-\xFF]+/', array(__CLASS__, 'html_encoding_callback'), $s);
		}

		if ('html-entities' === $from_encoding)
		{
			$s = html_entity_decode($s, ENT_COMPAT, 'UTF-8');
			$from_encoding = 'UTF-8';
		}

		return iconv($from_encoding, $to_encoding . '//IGNORE', $s);
	}

	static function decode_mimeheader($s)
	{
		return iconv_mime_decode($s, 2, self::$internal_encoding . '//IGNORE');
	}

	static function encode_mimeheader($s, $charset = INF, $transfer_encoding = INF, $linefeed = INF, $indent = INF)
	{
		trigger_error('mb_encode_mimeheader() is bugged. Please use iconv_mime_encode() instead.');
	}


	static function convert_case($s, $mode, $encoding = INF)
	{
		if ('' === $s) return '';

		INF === $encoding && $encoding = self::$internal_encoding;
		if ('UTF-8' === strtoupper($encoding)) $encoding = INF;
		else $s = iconv($encoding, 'UTF-8//IGNORE', $s);

		switch ($mode)
		{
		case MB_CASE_TITLE:
			$s = preg_replace_callback('/\b\p{Ll}/u', array(__CLASS__, 'title_case_callback'), $s);
			return INF === $encoding ? $s : iconv('UTF-8', $encoding, $s);

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

		return INF === $encoding ? $s : iconv('UTF-8', $encoding, $s);
	}

	static function internal_encoding($encoding = INF)
	{
		if (INF === $encoding) return self::$internal_encoding;

		if ('UTF-8' === strtoupper($encoding) || false !== @iconv($encoding, $encoding, ' '))
		{
			self::$internal_encoding = $encoding;
			return true;
		}

		return false;
	}

	static function list_encodings()
	{
		return array('UTF-8');
	}

	static function strlen($s, $encoding = INF)
	{
		INF === $encoding && $encoding = self::$internal_encoding;
		return iconv_strlen($s, $encoding . '//IGNORE');
	}

	static function strpos ($haystack, $needle, $offset = 0, $encoding = INF)
	{
		INF === $encoding && $encoding = self::$internal_encoding;
		return iconv_strpos($haystack, $needle, $offset, $encoding . '//IGNORE');
	}

	static function strrpos($haystack, $needle, $encoding = INF)
	{
		INF === $encoding && $encoding = self::$internal_encoding;
		return iconv_strrpos($haystack, $needle, $encoding . '//IGNORE');
	}

	static function strtolower($s, $encoding = INF)
	{
		return self::convert_case($s, MB_CASE_LOWER, $encoding);
	}

	static function strtoupper($s, $encoding = INF)
	{
		return self::convert_case($s, MB_CASE_UPPER, $encoding);
	}

	static function substitute_character($c = INF)
	{
		return INF !== $c ? false : 'none';
	}

	static function substr($s, $start, $length = PHP_INT_MAX, $encoding = INF)
	{
		INF === $encoding && $encoding = self::$internal_encoding;
		return iconv_substr($s, $start, $length, $encoding . '//IGNORE');
	}


	protected static function loadCaseTable($upper)
	{
		return unserialize(file_get_contents(
			$upper
				? patchworkPath('data/utf8/upperCase.ser')
				: patchworkPath('data/utf8/lowerCase.ser')
		));
	}

	protected static function html_encoding_callback($m)
	{
		return htmlentities($m, ENT_COMPAT, 'UTF-8');
	}

	protected static function title_case_callback($s)
	{
		$s = self::convert_case($s[0], MB_CASE_UPPER, 'UTF-8');

		$len = strlen($s);
		for ($i = 1; $i < $len && $s[$i] < "\x80"; ++$i) $s[$i] = strtolower($s[$i]);

		return $s;
	}
}
