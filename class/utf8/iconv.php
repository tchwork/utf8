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
 * XXX Not really useful now, needs real implementation
 */

class iconv
{
	static protected

	$input_encoding = 'UTF-8',
	$output_encoding = 'UTF-8',
	$internal_encoding = 'UTF-8';


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

	static function mime_encode($field_name, $field_value, $preferences = null)
	{
		if (null === $preferences)
		{
			$preferences = array(
				'scheme' => 'B',
				'input-charset' => self::$internal_encoding,
				'output-charset' => self::$internal_encoding,
				'line-length' => 76,
				'line-break-chars' => "\r\n"
			);
		}

		if (function_exists('mb_encode_mimeheader'))
		{
			$a = mb_encode_mimeheader(
				$field_value,
				$preferences['input-charset'],
				$preferences['scheme'],
				$preferences['line-break-chars']
			);

			if ('' !== (string) $field_name) $a = $field_name . ': ' . $a;

			return $a;
		}
		else W('iconv::mime_encode() not supported without mbstring');

		return false;
	}

	static function iconv($in_charset, $out_charset, $str)
	{
		W('iconv::iconv() not implemented');

		return false;
	}

	static function ob_handler($buffer, $mode)
	{
		// This quick implementation is not always safe for internal multibyte encodings
		return self::iconv(self::$internal_encoding, self::$output_encoding, $buffer);
	}
}
