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


class patchwork_unicode_compiler
{
	static

	$utf8Data,
	$DerivedNormalizationProps = 'http://www.unicode.org/Public/UNIDATA/DerivedNormalizationProps.txt',
	$UnicodeData               = 'http://www.unicode.org/Public/UNIDATA/UnicodeData.txt',
	$CompositionExclusions     = 'http://www.unicode.org/Public/UNIDATA/CompositionExclusions.txt',
	$CaseFolding               = 'http://www.unicode.org/Public/UNIDATA/CaseFolding.txt';


	static function __constructStatic()
	{
		set_time_limit(0);

		self::$utf8Data = patchworkPath('data/utf8/');
/** /
		self::$DerivedNormalizationProps = patchworkPath('data/utf8/DerivedNormalizationProps.txt');
		self::$UnicodeData               = patchworkPath('data/utf8/UnicodeData.txt');
		self::$CompositionExclusions     = patchworkPath('data/utf8/CompositionExclusions.txt');
		self::$CaseFolding               = patchworkPath('data/utf8/CaseFolding.txt');
/**/
	}


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

				$map[$data[1]] = self::chr($data[2]);
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
		foreach ($data as $data) $map[self::chr(hexdec($data[1]))] = $data[2];

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
						$k = self::chr($k);

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


	// Generate regular expression from unicode database
	// to check if an UTF-8 string needs normalization
	// $type = 'NFC' | 'NFD' | 'NFKC' | 'NFKD'

	static function quickCheck($type)
	{
		$rx = '';

		$h = fopen(self::$DerivedNormalizationProps, 'rt');
		while (false !== $m = fgets($h))
		{
			if (preg_match('/^([0-9A-F]+(?:\.\.[0-9A-F]+)?)\s*;\s*' . $type . '_QC\s*;\s*[MN]/', $m, $m))
			{
				$m = explode('..', $m[1]);
				$rx .= '\x{' . $m[0] . '}' . (isset($m[1]) ? (hexdec($m[0])+1 == hexdec($m[1]) ? '' : '-') . '\x{' . $m[1] . '}' : '');
			}
		}
		fclose($h);

		$rx = self::optimizeRx($rx . self::combiningCheck());

		return $rx;
	}


	// Generate regular expression from unicode database
	// to check if an UTF-8 string contains combining chars

	static function combiningCheck()
	{
		$rx = '';

		$lastChr = '';
		$lastOrd = 0;
		$interval = 0;

		$h = fopen(self::$UnicodeData, 'rt');
		while (false !== $m = fgets($h))
		{
			if (preg_match('/^([0-9A-F]+);[^;]*;[^;]*;([1-9]\d*)/', $m, $m))
			{
				$rx .= '\x{' . $m[1] . '}';
			}
		}
		fclose($h);

		$rx = self::optimizeRx($rx);

		return $rx;
	}


	// Write the 4+1 above regular expressions to disk

	static function quickChecks()
	{
		$a = '# Generated by patchwork_unicode_compiler::quickChecks()'
			. "\n" . self::quickCheck('NFC' )
			. "\n" . self::quickCheck('NFKC')
			. "\n" . self::quickCheck('NFD' )
			. "\n" . self::quickCheck('NFKD')
			. "\n" . self::combiningCheck();

		$a = preg_replace_callback("'\\\\x\\{([0-9A-Fa-f]+)\\}'", array(__CLASS__, 'chr_callback'), $a);

		file_put_contents(self::$utf8Data . 'quickChecks.txt', $a);
	}


	// Write unicode data maps to disk

	static function unicodeMaps()
	{
		$upperCase = array();
		$lowerCase = array();
		$caseFolding = array();
		$combiningClass = array();
		$canonicalComposition = array();
		$canonicalDecomposition = array();
		$compatibilityDecomposition = array();


		$exclusion = array();

		$h = fopen(self::$CompositionExclusions, 'rt');
		while (false !== $m = fgets($h))
		{
			if (preg_match('/^(?:# )?([0-9A-F]+) /', $m, $m))
			{
				$exclusion[self::chr(hexdec($m[1]))] = 1;
			}
		}
		fclose($h);


		$h = fopen(self::$UnicodeData, 'rt');
		while (false !== $m = fgets($h))
		{
			$m = explode(';', $m);

			$k = self::chr(hexdec($m[0]));
			$combClass = (int) $m[3];
			$decomp = $m[5];

			$m[12] && $m[12]!=$m[0] && $upperCase[$k] = self::chr(hexdec($m[12]));
			$m[13] && $m[13]!=$m[0] && $lowerCase[$k] = self::chr(hexdec($m[13]));

			$combClass && $combiningClass[$k] = $combClass;

			if ($decomp)
			{
				$canonic = '<' != $decomp[0];
				$canonic || $decomp = preg_replace("'^<.*> '", '', $decomp);

				$decomp = explode(' ', $decomp);

				$exclude = count($decomp) == 1 || isset($exclusion[$k]);

				$decomp = array_map('hexdec', $decomp);
				$decomp = array_map(array(__CLASS__,'chr'), $decomp);
				$decomp = implode('', $decomp);

				if ($canonic)
				{
					$canonicalDecomposition[$k] = $decomp;
					$exclude || $canonicalComposition[$decomp] = $k;
				}

				$compatibilityDecomposition[$k] = $decomp;
			}
		}
		fclose($h);

		do
		{
			$m = 0;

			foreach($canonicalDecomposition as $k => $decomp)
			{
				$h = strtr($decomp, $canonicalDecomposition);
				if ($h != $decomp)
				{
					$canonicalDecomposition[$k] = $h;
					$m = 1;
				}
			}
		}
		while ($m);

		do
		{
			$m = 0;

			foreach($compatibilityDecomposition as $k => $decomp)
			{
				$h = strtr($decomp, $compatibilityDecomposition);
				if ($h != $decomp)
				{
					$compatibilityDecomposition[$k] = $h;
					$m = 1;
				}
			}
		}
		while ($m);

		foreach($compatibilityDecomposition as $k => $decomp)
		{
			if (isset($canonicalDecomposition[$k]) && $canonicalDecomposition[$k] == $decomp) unset($compatibilityDecomposition[$k]);
		}


		$h = fopen(self::$CaseFolding, 'rt');
		while (false !== $m = fgets($h))
		{
			if (preg_match('/^([0-9A-F]+); ([CFST]); ([0-9A-F]+(?: [0-9A-F]+)*)/', $m, $m))
			{
				$k = self::chr(hexdec($m[1]));

				$decomp = explode(' ', $m[3]);
				$decomp = array_map('hexdec', $decomp);
				$decomp = array_map(array(__CLASS__,'chr'), $decomp);
				$decomp = implode('', $decomp);

				@($lowerCase[$k] != $decomp && $caseFolding[$m[2]][$k] = $decomp);
			}
		}
		fclose($h);

		// Only full case folding is worth serializing
		$caseFolding = array(
			array_keys(  $caseFolding['F']),
			array_values($caseFolding['F'])
		);
	
		$upperCase                  = serialize($upperCase);
		$lowerCase                  = serialize($lowerCase);
		$caseFolding                = serialize($caseFolding);
		$combiningClass             = serialize($combiningClass);
		$canonicalComposition       = serialize($canonicalComposition);
		$canonicalDecomposition     = serialize($canonicalDecomposition);
		$compatibilityDecomposition = serialize($compatibilityDecomposition);

		file_put_contents(self::$utf8Data . 'upperCase.ser'                 , $upperCase);
		file_put_contents(self::$utf8Data . 'lowerCase.ser'                 , $lowerCase);
		file_put_contents(self::$utf8Data . 'caseFolding_full.ser'          , $caseFolding);
		file_put_contents(self::$utf8Data . 'combiningClass.ser'            , $combiningClass);
		file_put_contents(self::$utf8Data . 'canonicalComposition.ser'      , $canonicalComposition);
		file_put_contents(self::$utf8Data . 'canonicalDecomposition.ser'    , $canonicalDecomposition);
		file_put_contents(self::$utf8Data . 'compatibilityDecomposition.ser', $compatibilityDecomposition);
	}


	protected static function optimizeRx($rx)
	{
		$rx = preg_replace_callback('/\\\\x\\{([0-9A-Fa-f]+)\\}-\\\\x\\{([0-9A-Fa-f]+)\\}/', array(__CLASS__, 'chr_range_callback'), $rx);

		preg_match_all('/[0-9A-Fa-f]+/', $rx, $rx);

		$rx = array_map('hexdec', $rx[0]);
		$rx = array_unique($rx);
		sort($rx);

		$a = '';
		$last = 0;
		$interval = 0;

		foreach ($rx as $rx)
		{
			if ($last+1 == $rx)
			{
				++$last;
				++$interval;
			}
			else
			{
				$interval && $a .= ($interval > 1 ? '-' : '') . '\x{' . dechex($last) . '}';

				$last = $rx;
				$interval = 0;

				$a .= '\x{' . dechex($rx) . '}';
			}
		}

		$interval && $a .= ($interval > 1 ? '-' : '') . '\x{' . dechex($last) . '}';

		return $a;
	}

	protected static function chr_callback($m) {return self::chr(hexdec($m[1]));}
	protected static function chr_range_callback($m) {return '\x{' . implode('}\x{', array_map('dechex', range(hexdec($m[1]), hexdec($m[2])))) . '}';}

	protected static function chr($c)
	{
		$c %= 0x200000;

		return $c < 0x80    ? chr($c) : (
		       $c < 0x800   ? chr(0xc0 | $c>> 6) . chr(0x80 | $c     & 0x3f) : (
		       $c < 0x10000 ? chr(0xe0 | $c>>12) . chr(0x80 | $c>> 6 & 0x3f) . chr(0x80 | $c    & 0x3f) : (
		                      chr(0xf0 | $c>>18) . chr(0x80 | $c>>12 & 0x3f) . chr(0x80 | $c>>6 & 0x3f) . chr(0x80 | $c & 0x3f)
		)));
	}
}

/**/if (!defined('patchwork'))
/**/{
		patchwork_unicode_compiler::__constructStatic();
/**/}
