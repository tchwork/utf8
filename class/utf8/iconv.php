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
 * Partial iconv implementation in pure PHP
 *
 * Not implemented:

iconv                     - Convert string to requested character encoding
iconv_mime_decode_headers - Decodes multiple MIME header fields at once
iconv_mime_decode         - Decodes a MIME header field


 * Implemented:
 
iconv_get_encoding - Retrieve internal configuration variables of iconv extension
iconv_set_encoding - Set current setting for character encoding conversion
iconv_mime_encode  - Composes a MIME header field
ob_iconv_handler   - Convert character encoding as output buffer handler
iconv_strlen       - Returns the character count of string
iconv_strpos       - Finds position of first occurrence of a needle within a haystack
iconv_strrpos      - Finds the last occurrence of a needle within a haystack
iconv_substr       - Cut out part of a string

 *
 */

class iconv
{
	static protected

	$input_encoding = 'UTF-8',
	$output_encoding = 'UTF-8',
	$internal_encoding = 'UTF-8';


	static function iconv($in_charset, $out_charset, $str)
	{
		W('iconv::iconv() not implemented');

		return false;
	}

	static function mime_decode_headers($encoded_headers, $mode = ICONV_MIME_DECODE_CONTINUE_ON_ERROR, $charset = null)
	{
		null === $charset && $charset = self::$internal_encoding;

		W('iconv::mime_decode_headers() not implemented');

		return false;
	}

	static function mime_decode($encoded_headers, $mode = ICONV_MIME_DECODE_CONTINUE_ON_ERROR, $charset = null)
	{
		null === $charset && $charset = self::$internal_encoding;

		W('iconv::mime_decode() not implemented');

		return false;
	}

	static function get_encoding($type = 'all')
	{
		switch ($type)
		{
		case 'input_encoding'   : return self::$input_encoding;
		case 'output_encoding'  : return self::$output_encoding;
		case 'internal_encoding': return self::$internal_encoding;
		}

		return array(
			'input_encoding'    => self::$input_encoding,
			'output_encoding'   => self::$output_encoding,
			'internal_encoding' => self::$internal_encoding
		);
	}

	static function set_encoding($type, $charset)
	{
		switch ($type)
		{
		case 'input_encoding'   : self::$input_encoding    = $charset; break;
		case 'output_encoding'  : self::$output_encoding   = $charset; break;
		case 'internal_encoding': self::$internal_encoding = $charset; break;

		default: return false;
		}

		return true;
	}

	static function mime_encode($field_name, $field_value, $pref = null)
	{
		is_array($pref) || $pref = array();

		$pref += array(
			'scheme'           => 'B',
			'input-charset'    => self::$internal_encoding,
			'output-charset'   => self::$internal_encoding,
			'line-length'      => 76,
			'line-break-chars' => "\r\n"
		);

		preg_match('/[\x80-\xFF]/', $field_name) && $field_name = '';

		$scheme = strtoupper(substr($pref['scheme'], 0, 1));
		$in  = strtoupper($pref['input-charset']);
		$out = strtoupper($pref['output-charset']);

		if ('UTF-8' !== $in && false === $field_value = iconv($in, 'UTF-8', $field_value)) return false;

		preg_match_all('/./us', $field_value, $chars);

		$chars = isset($chars[0]) ? $chars[0] : array();

		$line_break  = (int) $pref['line-length'];
		$line_start  = "=?{$pref['output-charset']}?{$scheme}?";
		$line_length = strlen($field_name) + 2 + strlen($line_start) + 2;
		$line_offset = strlen($line_start) + 3;
		$line_data   = '';

		$field_value = array();

		$Q = 'Q' === $scheme;

		foreach ($chars as &$c)
		{
			if ('UTF-8' !== $out && false === $c = iconv('UTF-8', $out, $c)) return false;

			$o = $Q
				? $c = preg_replace(
					'/[=_\?\x00-\x1F\x80-\xFF]/e',
					'"=".strtoupper(dechex(ord("\0")))',
					$c
				)
				: base64_encode($line_data . $c);

			if ($line_length + strlen($o) > $line_break)
			{
				$Q || $line_data = base64_encode($line_data);
				$field_value[] = $line_start . $line_data . '?=';
				$line_length = $line_offset;
				$line_data = '';
			}

			$line_data .= $c;
			$Q && $line_length += strlen($c);
		}

		if ('' !== $line_data)
		{
			$Q || $line_data = base64_encode($line_data);
			$field_value[] = $line_start . $line_data . '?=';
		}

		return $field_name . ': ' . implode($pref['line-break-chars'] . ' ', $field_value);
	}

	static function ob_handler($buffer, $mode)
	{
		return self::iconv(self::$internal_encoding, self::$output_encoding, $buffer);
	}

	static function strlen($s, $encoding) {return mb_strlen($s, $encoding);}
	static function strpos ($haystack, $needle, $offset = 0, $encoding = null) {return mb_strpos ($haystack, $needle, $offset, $encoding);}
	static function strrpos($haystack, $needle, $encoding = null) {return mb_strrpos($haystack, $needle, $encoding);}
	static function substr($s, $start, $length = null, $encoding = null) {return mb_substr($s, $start, $length, $encoding);}
}
