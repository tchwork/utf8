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


class
{
	// Generate regular expression from latest official unicode database
	// to check if an UTF-8 string needs canonical normalization form C

	static function notNFCrx()
	{
		$rx = '';

		$h = fopen('http://www.unicode.org/Public/UNIDATA/DerivedNormalizationProps.txt', 'rt');
		while (false !== $line = fgets($h))
		{
			if (preg_match('/^([0-9A-F]+(?:\.\.[0-9A-F]+)?)\s*;\s*NFC_QC\s*;\s*[MN]/', $line, $m))
			{
				$m = explode('..', $m[1]);
				$rx .= '\x' . $m[0] . (isset($m[1]) ? (hexdec($m[0])+1 == hexdec($m[1]) ? '' : '-') . '\x' . $m[1] : '');
			}
		}

		fclose($h);

		return $rx;
	}
}
