<?php /*********************************************************************
 *
 *   Copyright : (C) 2007 Nicolas Grekas. All rights reserved.
 *   Email     : p@tchwork.org
 *   License   : http://www.gnu.org/licenses/lgpl.txt GNU/LGPL
 *
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public
 *   License as published by the Free Software Foundation; either
 *   version 3 of the License, or (at your option) any later version.
 *
 ***************************************************************************/


class utf8_charset_compiler
{
	// See http://unicode.org/Public/MAPPINGS/ for conversion maps

	static function charsetMaps()
	{
		$map_dir = patchworkPath('data/unicode/charset/');
		$out_dir = patchworkPath('data/utf8/charset/');

		$h = opendir($map_dir);
		while (false !== $f = readdir($h)) if (false === strpos($f, '.') && is_file($map_dir . $f))
		{
			$data = file_get_contents($map_dir . $f);
			preg_match_all('/^0x([0-9a-f]+)[ \t]+0x([0-9a-f]+)/mi', $data, $data, PREG_SET_ORDER);

			$map = array();
			foreach ($data as $data)
			{
				$data = array_map('hexdec', $data);
				$data[1] = $data[1] > 255
					? chr($data[1]>>8) . chr($data[1]%256)
					: chr($data[1]);

				$map[$data[1]] = u::chr($data[2]);
			}

			file_put_contents("{$out_dir}from.{$f}.ser", serialize($map));
		}
		closedir($h);
	}


	// See http://www.gnu.org/software/libiconv/ for translit.def

	static function translitMap()
	{
		$data    = patchworkPath('data/unicode/charset/translit.def');
		$out_dir = patchworkPath('data/utf8/charset/');

		$data = file_get_contents($data);
		preg_match_all('/^([0-9a-f]+)\t([^\t]+)\t/mi', $data, $data, PREG_SET_ORDER);

		$map = array();
		foreach ($data as $data) $map[u::chr(hexdec($data[1]))] = $data[2];

		file_put_contents("{$out_dir}translit.ser", serialize($map));
	}


	// See http://unicode.org/Public/MAPPINGS/VENDORS/MICSFT/WindowsBestFit/ for mappings

	static function bestFit()
	{
		$map_dir = patchworkPath('data/unicode/charset/');
		$out_dir = patchworkPath('data/utf8/charset/');

		$h = opendir($map_dir);
		while (false !== $f = readdir($h)) if (0 === strpos($f, 'bestfit') && preg_match('/^bestfit\d+\.txt$/D', $f))
		{
			$map = array();
			$out = substr($f, 0, -3) .'ser';

			$f = fopen($map_dir . $f, 'rb');

			while (false !== $s = fgets($f)) if (0 === strpos($s, 'WCTABLE'))
			{
				while (false !== $s = fgets($f))
				{
					if (0 === strpos($s, 'ENDCODEPAGE')) break;

					$s = explode("\t", rtrim($s));

					if (isset($s[1]))
					{
						$k = hexdec(substr($s[0], 2));
						$k = u::chr($k);

						$v = substr($s[1], 2);
						$v = chr(hexdec(substr($v, 0, 2))) . (4 === strlen($v) ? chr(hexdec(substr($v, 0, 4))) : '');

						$map[$k] = $v;
					}
				}

				break;
			}

			fclose($f);

			file_put_contents($out_dir . $out, serialize($map));
		}
		closedir($h);
	}
}
