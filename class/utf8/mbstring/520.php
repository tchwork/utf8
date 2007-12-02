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
 * All functions introduced in PHP 5.2.0:

mb_stripos  - Finds position of first occurrence of a string within another, case insensitive
mb_stristr  - Finds first occurrence of a string within another, case insensitive
mb_strrchr  - Finds the last occurrence of a character in a string within another
mb_strrichr - Finds the last occurrence of a character in a string within another, case insensitive
mb_strripos - Finds position of last occurrence of a string within another, case insensitive
mb_strstr   - Finds first occurrence of a string within another

 */


function mb_stripos( $haystack, $needle, $offset = 0,   $encoding = INF) {return utf8_mbstring_520::stripos( $haystack, $needle, $offset, $encoding);}
function mb_stristr( $haystack, $needle, $part = false, $encoding = INF) {return utf8_mbstring_520::stristr( $haystack, $needle, $part,   $encoding);}
function mb_strrchr( $haystack, $needle, $part = false, $encoding = INF) {return utf8_mbstring_520::strrchr( $haystack, $needle, $part,   $encoding);}
function mb_strrichr($haystack, $needle, $part = false, $encoding = INF) {return utf8_mbstring_520::strrichr($haystack, $needle, $part,   $encoding);}
function mb_strripos($haystack, $needle, $offset = 0,   $encoding = INF) {return utf8_mbstring_520::strripos($haystack, $needle, $offset, $encoding);}
function mb_strstr(  $haystack, $needle, $part = false, $encoding = INF) {return utf8_mbstring_520::strstr(  $haystack, $needle, $part,   $encoding);}


class utf8_mbstring_520
{
	static function stripos($haystack, $needle, $offset = 0, $encoding = INF)
	{
		INF === $encoding && $encoding = mb_internal_encoding();
		return mb_strpos(mb_strtolower($haystack, $encoding), mb_strtolower($needle, $encoding), $offset, $encoding);
	}

	static function stristr($haystack, $needle, $part = false, $encoding = INF)
	{
		$pos = self::stripos($haystack, $needle, $encoding);
		return self::getSubpart($pos, $part, $haystack, $encoding);
	}

	static function strrchr($haystack, $needle, $part = false, $encoding = INF)
	{
		$pos = self::strrpos($haystack, $needle, 0, $encoding);
		return self::getSubpart($pos, $part, $haystack, $encoding);
	}

	static function strrichr($haystack, $needle, $part = false, $encoding = INF)
	{
		$pos = self::strripos($haystack, $needle, $encoding);
		return self::getSubpart($pos, $part, $haystack, $encoding);
	}

	static function strripos($haystack, $needle, $offset = 0, $encoding = INF)
	{
		INF === $encoding && $encoding = mb_internal_encoding();
		return self::strrpos(mb_strtolower($haystack, $encoding), mb_strtolower($needle, $encoding), $offset, $encoding);
	}

	static function strstr($haystack, $needle, $part = false, $encoding = INF)
	{
		$pos = strpos($haystack, $needle);
		return false === $pos ? false : ($part ? substr($haystack, 0, $pos) : substr($haystack, $pos));
	}

	static function strrpos($haystack, $needle, $offset = 0, $encoding = INF)
	{
		INF === $encoding && $encoding = mb_internal_encoding();

		if ($offset != (int) $offset)
		{
			$offset = 0;
		}
		else if ($offset = (int) $offset)
		{
			$haystack = mb_substr($haystack, $offset, PHP_INT_MAX, $encoding);
		}

		$pos = mb_strrpos_500($haystack, $needle, $encoding);

		return false !== $pos ? $offset + $pos : false;
	}


	protected static function getSubpart($pos, $part, $haystack, $encoding)
	{
		INF === $encoding && $encoding = mb_internal_encoding();

		return false === $pos ? false : (
			  $part
			? mb_substr($haystack,    0,        $pos, $encoding)
			: mb_substr($haystack, $pos, PHP_INT_MAX, $encoding)
		);
	}
}

#>>> For non patchwork code
if (extension_loaded('iconv'))
{
	function mb_strrpos_500($haystack, $needle, $encoding = INF)
	{
		INF === $encoding && $encoding = mb_internal_encoding();
		return iconv_strrpos($haystack, $needle, $encoding);
	}
}
else
{
	function mb_strrpos_500($haystack, $needle, $encoding = INF)
	{
		return utf8_mbstring_500::strrpos($haystack, $needle, $encoding);
	}
}
#<<<
