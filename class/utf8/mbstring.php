<?php /*********************************************************************
 *
 *   Copyright : (C) 2007 Nicolas Grekas. All rights reserved.
 *   Email     : nicolas.grekas+patchwork@espci.org
 *   License   : http://www.gnu.org/licenses/lgpl.txt GNU/LGPL, see LGPL
 *
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public
 *   License as published by the Free Software Foundation; either
 *   version 3 of the License, or (at your option) any later version.
 *
 ***************************************************************************/


// No class here, only functions to wrap our mbstring replacement for non patchwork code


if (function_exists('mb_stripos')) return;

{ // These functions were introduced in PHP 5.2.0
function mb_stripos( $haystack, $needle, $offset = 0,   $encoding = null) {return utf8_mbstring_520::stripos( $haystack, $needle, $offset, $encoding);}
function mb_stristr( $haystack, $needle, $part = false, $encoding = null) {return utf8_mbstring_520::stristr( $haystack, $needle, $part,   $encoding);}
function mb_strrchr( $haystack, $needle, $part = false, $encoding = null) {return utf8_mbstring_520::strrchr( $haystack, $needle, $part,   $encoding);}
function mb_strrichr($haystack, $needle, $part = false, $encoding = null) {return utf8_mbstring_520::strrichr($haystack, $needle, $part,   $encoding);}
function mb_strripos($haystack, $needle, $offset = 0,   $encoding = null) {return utf8_mbstring_520::strripos($haystack, $needle, $offset, $encoding);}
function mb_strstr(  $haystack, $needle, $part = false, $encoding = null) {return utf8_mbstring_520::strstr(  $haystack, $needle, $part,   $encoding);}
}


if (function_exists('mb_strpos')) return;

{
function mb_convert_case($str, $mode, $encoding = null)                   {return utf8_mbstring_500::convert_case($str, $mode, $encoding);}
function mb_list_encodings()                                              {return utf8_mbstring_500::list_encodings();}
function mb_strlen($str, $encoding = null)                                {return utf8_mbstring_500::strlen($str, $encoding);}
function mb_strpos($haystack, $needle, $offset = 0, $encoding = null)     {return utf8_mbstring_500::strpos($haystack, $needle, $offset, $encoding);}
function mb_strtolower($str, $encoding = null)                            {return utf8_mbstring_500::strtolower($str, $encoding);}
function mb_strtoupper($str, $encoding = null)                            {return utf8_mbstring_500::strtoupper($str, $encoding);}
function mb_substr($str, $start, $length = null, $encoding = null)        {return utf8_mbstring_500::substr($str, $start, $length, $encoding);}
function mb_strrpos( $haystack, $needle, $offset = 0,   $encoding = null) {return utf8_mbstring_520::strrpos( $haystack, $needle, $offset, $encoding);}
function mb_convert_encoding($str, $to_encoding, $from_encoding = null)   {return utf8_mbstring_500::convert_encoding($str, $to_encoding, $from_encoding);}
function mb_decode_mimeheader($str)                                       {return utf8_mbstring_500::decode_mimeheader($str);}
function mb_encode_mimeheader($str, $charset = null, $transfer_encoding = null, $linefeed = null, $indent = null)
{
	return utf8_mbstring_500::encode_mimeheader($str, $charset, $transfer_encoding, $linefeed, $indent);
}
}

// utf8_mbstring_500_strrpos() is used internally by utf8_mbstring_520::strrpos()
if (function_exists('iconv_strrpos'))
{
	function utf8_mbstring_500_strrpos($haystack, $needle, $encoding)
	{
		return iconv_strrpos($haystack, $needle, $encoding);
	}
}
else
{
	function utf8_mbstring_500_strrpos($haystack, $needle, $encoding)
	{
		$needle = mb_substr($needle, 0, 1);
		$pos = strpos(strrev($haystack), strrev($needle));
		return false === $pos ? false : mb_strlen($pos ? substr($haystack, 0, -$pos) : $haystack);
	}
}
