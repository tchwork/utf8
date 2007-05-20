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


class u // u for utf8
{
	static function strlen($str)     {return mb_strlen($str, 'UTF-8');}
	static function strtolower($str) {return mb_strtolower($str, 'UTF-8');}
	static function strtoupper($str) {return mb_strtoupper($str, 'UTF-8');}
	static function substr  ($str, $start, $length = null)      {return mb_substr  ($str, $start, $length, 'UTF-8');}
	static function strpos  ($haystack, $needle, $offset = 0)   {return mb_strpos  ($haystack, $needle, $offset, 'UTF-8');}
	static function strrpos ($haystack, $needle, $offset = 0)   {return mb_strrpos ($haystack, $needle, $offset, 'UTF-8');}
	static function stripos ($haystack, $needle, $offset = 0)   {return mb_stripos ($haystack, $needle, $offset, 'UTF-8');}
	static function strripos($haystack, $needle, $offset = 0)   {return mb_strripos($haystack, $needle, $offset, 'UTF-8');}
	static function stristr ($haystack, $needle, $part = false) {return mb_stristr ($haystack, $needle, $part  , 'UTF-8');}
	static function strrchr ($haystack, $needle, $part = false) {return mb_strrchr ($haystack, $needle, $part  , 'UTF-8');}
	static function strrichr($haystack, $needle, $part = false) {return mb_strrichr($haystack, $needle, $part  , 'UTF-8');}
	static function strstr  ($haystack, $needle, $part = false) {return mb_strstr  ($haystack, $needle, $part  , 'UTF-8');}
}
