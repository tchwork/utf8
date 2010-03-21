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
 * iconv implementation in pure PHP
 *
 * Implemented:
 
iconv              - Convert string to requested character encoding
iconv_mime_decode  - Decodes a MIME header field
iconv_mime_decode_headers - Decodes multiple MIME header fields at once
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


if (!function_exists('iconv'))
{
	define('ICONV_IMPL', 'patchwork');
	define('ICONV_VERSION', '1.0');
	define('ICONV_MIME_DECODE_STRICT', 1);
	define('ICONV_MIME_DECODE_CONTINUE_ON_ERROR', 2);


	function iconv($in_charset, $out_charset, $str) {return utf8_iconv::iconv($in_charset, $out_charset, $str);}
	function iconv_mime_decode_headers($encoded_headers, $mode = 2, $charset = INF) {return utf8_iconv::mime_decode_headers($encoded_headers, $mode, $charset);}
	function iconv_mime_decode($encoded_headers, $mode = 2, $charset = INF)         {return utf8_iconv::mime_decode        ($encoded_headers, $mode, $charset);}
	function iconv_get_encoding($type = 'all')   {return utf8_iconv::get_encoding($type);}
	function iconv_set_encoding($type, $charset) {return utf8_iconv::set_encoding($type, $charset);}
	function iconv_mime_encode($field_name, $field_value, $pref = INF) {return utf8_iconv::mime_encode($field_name, $field_value, $pref);}
	function ob_iconv_handler($buffer, $mode)  {return utf8_iconv::ob_handler($buffer, $mode);}
	function iconv_strpos ($haystack, $needle, $offset = 0, $encoding = INF) {return utf8_iconv::strpos ($haystack, $needle, $offset, $encoding);}
	function iconv_strrpos($haystack, $needle,              $encoding = INF) {return utf8_iconv::strrpos($haystack, $needle,          $encoding);}
	function iconv_substr($s, $start, $length = PHP_INT_MAX, $encoding = INF) {return utf8_iconv::substr($s, $start, $length, $encoding);}

	if (extension_loaded('xml'))
	{
		function iconv_strlen($s, $encoding = INF) {return utf8_iconv::strlen1($s, $encoding);}
	}
	else
	{
		function iconv_strlen($s, $encoding = INF) {return utf8_iconv::strlen2($s, $encoding);}
	}
}


class utf8_iconv
{
	const

	ERROR_ILLEGAL_CHARACTER = 'utf8_iconv::iconv(): Detected an illegal character in input string',
	ERROR_WRONG_CHARSET     = 'utf8_iconv::iconv(): Wrong charset, conversion from `%s\' to `%s\' is not allowed';


	static protected

	$input_encoding = 'UTF-8//IGNORE',
	$output_encoding = 'UTF-8//IGNORE',
	$internal_encoding = 'UTF-8//IGNORE',

	$alias = array(
		'ascii'   => 'us-ascii',
		'tis-620' => 'iso-8859-11',
	),

	$translit_map = array(),
	$convert_map = array(),

	$utf_len_mask = array("\xC0" => 2, "\xD0" => 2, "\xE0" => 3, "\xF0" => 4),
	$is_valid_utf8;


	static function iconv($in_charset, $out_charset, $str)
	{
		if ('' === (string) $str) return '';


		// Prepare for //IGNORE and //TRANSLIT

		$TRANSLIT = $IGNORE = '';

		$out_charset = strtolower($out_charset);
		$in_charset  = strtolower($in_charset );

		'' === $out_charset && $out_charset = 'iso-8859-1';
		'' ===  $in_charset &&  $in_charset = 'iso-8859-1';

		if ('//translit' === substr($out_charset, -10))
		{
			$TRANSLIT = '//TRANSLIT';
			$out_charset = substr($out_charset, 0, -10);
		}

		if ('//ignore' === substr($out_charset, -8))
		{
			$IGNORE = '//IGNORE';
			$out_charset = substr($out_charset, 0, -8);
		}

		'//translit' === substr($in_charset, -10) && $in_charset = substr($in_charset, 0, -10);
		'//ignore'   === substr($in_charset,  -8) && $in_charset = substr($in_charset, 0,  -8);

		isset(self::$alias[ $in_charset]) &&  $in_charset = self::$alias[ $in_charset];
		isset(self::$alias[$out_charset]) && $out_charset = self::$alias[$out_charset];


		// Load charset maps

		if (   ('utf-8' !==  $in_charset && !self::loadMap('from.',  $in_charset,  $in_map))
		    || ('utf-8' !== $out_charset && !self::loadMap(  'to.', $out_charset, $out_map)) )
		{
			trigger_error(sprintf(self::ERROR_WRONG_CHARSET, $in_charset, $out_charset));
			return false;
		}


		if ('utf-8' !== $in_charset)
		{
			// Convert input to UTF-8

			ob_start();

			$str = 2 === count($in_map)
				? call_user_func_array($in_map, array(&$str, $IGNORE, $in_charset))
				: self::map_to_utf8($in_map, $str, $IGNORE);

			$str = !$str && ob_end_clean() || 1 ? false : ob_get_clean();

			self::$is_valid_utf8 = true;
		}
		else
		{
			self::$is_valid_utf8 = preg_match('//u', $str);

			if (!self::$is_valid_utf8 && !$IGNORE)
			{
				trigger_error(self::ERROR_ILLEGAL_CHARACTER);
				return false;
			}

			if ('utf-8' === $out_charset)
			{
				// UTF-8 validation

				$str = self::utf8_to_utf8($str, $IGNORE);
			}
		}

		if ('utf-8' !== $out_charset && false !== $str)
		{
			// Convert output to UTF-8

			ob_start();

			$str = 2 === count($out_map)
				? call_user_func_array($out_map, array(&$str, $IGNORE, $TRANSLIT, $out_charset))
				: self::map_from_utf8($out_map, $str, $IGNORE, $TRANSLIT);

			$str = !$str && ob_end_clean() || 1 ? false : ob_get_clean();
		}

		return $str;
	}

	static function mime_decode_headers($str, $mode = ICONV_MIME_DECODE_CONTINUE_ON_ERROR, $charset = INF)
	{
		INF === $charset && $charset = self::$internal_encoding;

		false !== strpos($str, "\r") && $str = strtr(str_replace("\r\n", "\n", $str), "\r", "\n");
		$str = explode("\n\n", $str, 2);

		$headers = array();

		$str = preg_split('/\n(?![ \t])/', $str[0]);
		foreach ($str as $str)
		{
			$str = self::mime_decode($str, $mode, $charset);
			$str = explode(':', $str, 2);

			if (2 === count($str))
			{
				if (isset($headers[$str[0]]))
				{
					is_array($headers[$str[0]]) || $headers[$str[0]] = array($headers[$str[0]]);
					$headers[$str[0]][] = ltrim($str[1]);
				}
				else $headers[$str[0]] = ltrim($str[1]);
			}
		}

		return $headers;
	}

	static function mime_decode($str, $mode = ICONV_MIME_DECODE_CONTINUE_ON_ERROR, $charset = INF)
	{
		INF === $charset && $charset = self::$internal_encoding;

		false !== strpos($str, "\r") && $str = strtr(str_replace("\r\n", "\n", $str), "\r", "\n");
		$str = preg_split('/\n(?![ \t])/', rtrim($str), 2);
		$str = preg_replace('/[ \t]*\n[ \t]+/', ' ', rtrim($str[0]));
		$str = preg_split('/=\?([^?]+)\?([bqBQ])\?(.*)\?=/', $str, -1, PREG_SPLIT_DELIM_CAPTURE);

		ob_start();

		echo self::iconv('UTF-8', $charset, $str[0]);

		$i = 1;
		$len = count($str);

		while ($i < $len)
		{
			$str[$i+2] = 'Q' === strtoupper($str[$i+1])
				? rawurldecode(strtr(str_replace('%', '%25', $str[$i+2]), '=_', '% '))
				: base64_decode($str[$i+2]);

			$str[$i+2] = self::iconv($str[$i], $charset, $str[$i+2]);
			$str[$i+3] = self::iconv('UTF-8' , $charset, $str[$i+3]);

			echo $str[$i+2], '' === trim($str[$i+3]) ? '' : $str[$i+3];

			$i += 4;
		}

		return ob_get_clean();
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

	static function mime_encode($field_name, $field_value, $pref = INF)
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

		if ('UTF-8' !== $in && false === $field_value = self::iconv($in, 'UTF-8', $field_value)) return false;

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
			if ('UTF-8' !== $out && false === $c = self::iconv('UTF-8', $out, $c)) return false;

			$o = $Q
				? $c = preg_replace_callback(
					'/[=_\?\x00-\x1F\x80-\xFF]/',
					array(__CLASS__, 'qp_byte_callback'),
					$c
				)
				: base64_encode($line_data . $c);

			if (isset($o[$line_break - $line_length]))
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

	static function strlen($s, $encoding = INF)
	{
		static $xml = null; null === $xml && $xml = extension_loaded('xml');
		return $xml
			? self::strlen1($s, $encoding)
			: self::strlen2($s, $encoding);
	}

	static function strlen1($s, $encoding = INF)
	{
		INF === $encoding && $encoding = self::$internal_encoding;
		'UTF-8' === strtoupper(substr($encoding, 0, 5)) || $s = self::iconv($encoding, 'UTF-8//IGNORE', $s);

		return strlen(utf8_decode($s));
	}

	static function strlen2($s, $encoding = INF)
	{
		INF === $encoding && $encoding = self::$internal_encoding;
		'UTF-8' === strtoupper(substr($encoding, 0, 5)) || $s = self::iconv($encoding, 'UTF-8//IGNORE', $s);

		$utf_len_mask = self::$utf_len_mask;

		$i = 0; $j = 0;
		$len = strlen($s);

		while ($i < $len)
		{
			$u = $s[$i] & "\xF0";
			$i += isset($utf_len_mask[$u]) ? $utf_len_mask[$u] : 1;
			++$j;
		}

		return $j;
	}

	static function strpos($haystack, $needle, $offset = 0, $encoding = INF)
	{
		INF === $encoding && $encoding = self::$internal_encoding;
		'UTF-8' === strtoupper(substr($encoding, 0, 5)) || $s = self::iconv($encoding, 'UTF-8//IGNORE', $s);

		if ($offset = (int) $offset) $haystack = self::substr($haystack, $offset, PHP_INT_MAX, 'UTF-8');
		$pos = strpos($haystack, $needle);
		return false === $pos ? false : ($offset + ($pos ? iconv_strlen(substr($haystack, 0, $pos), 'UTF-8') : 0));
	}

	static function strrpos($haystack, $needle, $encoding = INF)
	{
		INF === $encoding && $encoding = self::$internal_encoding;
		'UTF-8' === strtoupper(substr($encoding, 0, 5)) || $s = self::iconv($encoding, 'UTF-8//IGNORE', $s);

		$needle = self::substr($needle, 0, 1, 'UTF-8');
		$pos = strpos(strrev($haystack), strrev($needle));
		return false === $pos ? false : iconv_strlen($pos ? substr($haystack, 0, -$pos) : $haystack, 'UTF-8');
	}

	static function substr($s, $start, $length = PHP_INT_MAX, $encoding = INF)
	{
		INF === $encoding && $encoding = self::$internal_encoding;
		if ('UTF-8' === strtoupper(substr($encoding, 0, 5))) $encoding = INF;
		else $s = self::iconv($encoding, 'UTF-8//IGNORE', $s);

		$slen = iconv_strlen($s, 'UTF-8');
		$start = (int) $start;

		if (0 > $start) $start += $slen;
		if (0 > $start) $start = 0;
		if ($start >= $slen) return '';

		$rx = $slen - $start;

		if (0 > $length) $length += $rx;
		if (0 >= $length) return '';

		if ($length > $slen - $start) $length = $rx;

		$rx = '/^' . ($start ? self::preg_offset($start) : '') . '(' . self::preg_offset($length) . ')/u';

		$s = preg_match($rx, $s, $s) ? $s[1] : '';

		return INF === $encoding ? $s : self::iconv('UTF-8', $encoding, $s);
	}


	protected static function loadMap($type, $charset, &$map)
	{
		$map =& self::$convert_map[$type . $charset];

		if (INF === $map)
		{
			$map = patchworkPath('data/utf8/charset/' . $type . $charset . '.ser');

			if (false === $map)
			{
				$rev_type = 'to.' === $type ? 'from.' : 'to.';
				$rev_map = patchworkPath('data/utf8/charset/' . $rev_type . $charset . '.ser');

				if (false !== $rev_map)
				{
					$rev_map = unserialize(file_get_contents($rev_map));
					self::$convert_map[$rev_type . $charset] =& $rev_map;
					if (2 === count($rev_map)) return false;
					else $map = array_reverse($rev_map);
				}
				else return false;
			}
			else $map = unserialize(file_get_contents($map));
		}

		return true;
	}

	protected static function utf8_to_utf8(&$str, $IGNORE)
	{
		$utf_len_mask = self::$utf_len_mask;
		$valid        = self::$is_valid_utf8;

		ob_start();

		$i = 0;
		$len = strlen($str);

		while ($i < $len)
		{
			if ($str[$i] < "\x80") echo $str[$i++];
			else
			{
				$utf_len = $s[$i] & "\xF0";
				$utf_len = isset($utf_len_mask[$utf_len]) ? $utf_len_mask[$utf_len] : 1;
				$utf_chr = substr($str, $i, $utf_len);

				if (1 === $utf_len || !($valid || preg_match('//u', $utf_chr)))
				{
					if ($IGNORE)
					{
						++$i;
						continue;
					}

					ob_end_clean();
					trigger_error(self::ERROR_ILLEGAL_CHARACTER);
					return false;
				}
				else $i += $utf_len;

				echo $utf_chr;
			}
		}

		return ob_get_clean();
	}

	protected static function map_to_utf8(&$map, &$str, $IGNORE)
	{
		$len = strlen($str);
		for ($i = 0; $i < $len; ++$i)
		{
			if (isset($map[$str[$i]])) echo $map[$str[$i]];
			else if (isset($map[$str[$i] . $str[$i+1]])) echo $map[$str[$i] . $str[++$i]];
			else if (!$IGNORE)
			{
				trigger_error(self::ERROR_ILLEGAL_CHARACTER);
				return false;
			}
		}

		return true;
	}

	protected static function map_from_utf8(&$map, &$str, $IGNORE, $TRANSLIT)
	{
		$utf_len_mask = self::$utf_len_mask;
		$valid        = self::$is_valid_utf8;

		$TRANSLIT
			&& self::$translit_map
			|| self::$translit_map = unserialize(file_get_contents(patchworkPath('data/utf8/charset/translit.ser')));

		$i = 0;
		$len = strlen($str);

		while ($i < $len)
		{
			if ($str[$i] < "\x80") $utf_chr = $str[$i++];
			else
			{
				$utf_len = $str[$i] & "\xF0";
				$utf_len = isset($utf_len_mask[$utf_len]) ? $utf_len_mask[$utf_len] : 1;
				$utf_chr = substr($str, $i, $utf_len);

				if ($IGNORE && (1 === $utf_len || !($valid || preg_match('//u', $utf_chr))))
				{
					++$i;
					continue;
				}
				else $i += $utf_len;
			}

			if (isset($map[$utf_chr]))
			{
				echo $map[$utf_chr];
			}
			else if ($TRANSLIT && isset($translit_map[$utf_chr]))
			{
				$utf_chr = $translit_map[$utf_chr];

				if (isset($map[$utf_chr]))
				{
					echo $map[$utf_chr];
				}
				else if (!self::map_from_utf8($map, $utf_chr, $IGNORE, true))
				{
					return false;
				}
			}
			else if (!$IGNORE)
			{
				trigger_error(self::ERROR_ILLEGAL_CHARACTER);
				return false;
			}
		}

		return true;
	}

	protected static function qp_byte_callback($m)
	{
		return '=' . strtoupper(dechex(ord($m[0])));
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
}
