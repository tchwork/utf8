<?php /*********************************************************************
 *
 *   Copyright : (C) 2006 Nicolas Grekas. All rights reserved.
 *   Email     : nicolas.grekas+patchwork@espci.org
 *   License   : http://www.gnu.org/licenses/lgpl.txt GNU/LGPL, see LGPL
 *
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public
 *   License as published by the Free Software Foundation; either
 *   version 3 of the License, or (at your option) any later version.
 *
 ***************************************************************************/


// See http://www.unicode.org/reports/tr15/

class
{
	protected static
		
		$K,
		$compose = false,
		
		$quickCheckNFC,
		$quickCheckNFD,
		$quickCheckNFKC,
		$quickCheckNFKD,
		
		$combiningCheck,
		
		$C,
		$D,
		$KD,
		
		$cC;


	static function __static_construct()
	{
		$a = file_get_contents(resolvePath('data/utf8/quickChecks.txt'));
		$a = explode("\n", $a);
		self::$quickCheckNFC  = $a[1];
		self::$quickCheckNFD  = $a[3];
		self::$quickCheckNFKC = $a[2];
		self::$quickCheckNFKD = $a[4];
		self::$combiningCheck = $a[5];
	}

	static function toNFC($s, $K = false)
	{
		self::$compose = true;
		self::$K = $K;
		$K ? ($K =& self::$quickCheckNFKC) : ($K =& self::$quickCheckNFC);
		$s = preg_replace_callback($K, array(__CLASS__, 'compose'), $s);
		self::$compose = false;

		return $s;
	}

	static function toNFD($s, $K = false)
	{
		self::$K = $K;
		$K ? ($K =& self::$quickCheckNFKD) : ($K =& self::$quickCheckNFD);
		$s = preg_replace_callback($K, array(__CLASS__, 'decompose'), $s);

		return $s;
	}

	static function toNFKC($s) {return self::toNFC($s, true);}
	static function toNFKD($s) {return self::toNFD($s, true);}


	// Some remaining chars for accents decomposition
	// from http://www.unicode.org/cldr/

	static $lig = array(
		'Æ' => 'AE', 'æ' => 'ae', 'ß' => 'ss', 'Œ' => 'OE', 'œ' => 'oe', 'ʤ' => 'dz',
		'ʣ' => 'dz', 'ʥ' => 'dz', 'ƕ' => 'hv', 'Ƣ' => 'OI', 'ƣ' => 'oi', 'ʨ' => 'tc',
		'ʦ' => 'ts', 'ƻ' => '2' , 'Ŋ' => 'NG', 'ŋ' => 'ng', 'Ð' => 'D' , 'ð' => 'd' ,
		'Ø' => 'O' , 'ø' => 'o' , 'Þ' => 'TH', 'þ' => 'th', 'Θ' => 'T' , 'θ' => 'T' ,
		'Ʃ' => 'SH', 'ʃ' => 'sh', 'Ʒ' => 'ZH', 'ʒ' => 'zh', 'Ʊ' => 'U' , 'ʊ' => 'u' ,
		'Ə' => 'A' , 'ə' => 'a' , 'Ɔ' => 'O' , 'ɔ' => 'o' , 'Ɛ' => 'E' , 'ɛ' => 'e' ,
		'ʔ' => '?' , 'ɪ' => 'i' , 'ʌ' => 'v',
	);

	static function removeAccents($s)
	{
		$s = self::toNFD($s, true);
		$s = preg_replace('/\Mn+/u', '', $s);
		$s = strtr($s, self::$lig);
		$s = self::toNFC($s);

		return $s;
	}


	// Internal protected methods

	protected static function compose($s)
	{
		isset(self::$C) || self::$C = unserialize(file_get_contents(resolvePath('data/utf8/canonicalComposition.ser')));

		// Decompose
		$s = self::toNFD($s[0], self::$K);

		// Recompose
		$s = preg_replace_callback(
			'/([^' . self::$combiningCheck . ']?)([' . self::$combiningCheck . ']{2,})/u',
			array(__CLASS__, 'composeCombining'),
			$s
		);
		$s = strtr($s, self::$C);

		// Compose Hangul chars
		$s = preg_replace_callback('/[\x{1100}-\x{1112}][\x{1161}-\x{1175}][\x{11a7}-\x{11C2}]?/u', array(__CLASS__, 'composeHangul'), $s);

		return $s;
	}

	protected static function decompose($s)
	{
		// Decompose
		isset(self::$D) || self::$D = unserialize(file_get_contents(resolvePath('data/utf8/canonicalDecomposition.ser')));
		$s = strtr($s[0], self::$D);

		if (self::$K)
		{
			isset(self::$KD) || self::$KD = unserialize(file_get_contents(resolvePath('data/utf8/compatibilityDecomposition.ser')));
			$s = strtr($s, self::$KD);
		}

		// Decompose Hangul chars
		$s = preg_replace_callback('/[\x{ac00}-\x{d7a3}]/u', array(__CLASS__, 'decomposeHangul'), $s);

		// Sort combining chars
		self::$compose || $s = preg_replace_callback('/[' . self::$combiningCheck . ']{2,}/u', array(__CLASS__, 'sortCombining'), $s);

		return $s;
	}


	protected static function decomposeHangul($s)
	{
		$s = unpack('C*', $s[0]);
		$i = (($s[1]-224) << 12) + (($s[2]-128) << 6) + $s[3] - 0xac80;

		$l = (int)  ($i / 588);
		$v = (int) (($i % 588) / 28);
		$t = $i % 28;

		$s = "\xe1\x84" . chr(0x80 + $l) . "\xe1\x85" . chr(0xa1 + $v);
		$t && $s .= $t >= 25 ? ("\xe1\x87" . chr(0x67 + $t)) : ("\xe1\x86" . chr(0xa7 + $t));

		return $s;
	}

	protected static function composeHangul($s)
	{
		$s = $s[0];

		$l = ord($s[2]) - 0x80;
		$v = ord($s[5]) - 0xa1;

		if (9 == strlen($s))
		{
			$t = ord($s[8]) - 0xa7;
			0 > $t && $t += 0x40;
		}
		else $t = 0;

		$l = 0xac00 + ($l * 21 + $v) * 28 + $t;
		return chr(0xe0 | $l>>12) . chr(0x80 | $l>> 6 & 0x3f) . chr(0x80 | $l & 0x3f);
	}


	protected static function sortCombining($s)
	{
		isset(self::$cC) || self::$cC = unserialize(file_get_contents(resolvePath('data/utf8/combiningClass.ser')));

		preg_match_all('/./u', $s[0], $s);

		$a = array();
		foreach ($s[0] as $s)
		{
			isset($a[self::$cC[$s]]) || $a[self::$cC[$s]] = '';
			$a[self::$cC[$s]] .= $s;
		}

		ksort($a);

		return implode('', $a);
	}

	protected static function composeCombining($s)
	{
		isset(self::$cC) || self::$cC = unserialize(file_get_contents(resolvePath('data/utf8/combiningClass.ser')));

		preg_match_all('/./u', $s[2], $c);
		$s = $s[1];

		$a = array();
		foreach ($c[0] as $c)
		{
			isset($a[self::$cC[$c]]) || $a[self::$cC[$c]] = array();
			$a[self::$cC[$c]][] = $c;
		}

		ksort($a);

		$lastClass = 0;

		foreach ($a as $class => &$chars)
		{
			foreach ($chars as &$c)
			{
				if ($lastClass == $class) ;
				else if (isset(self::$C[$s . $c]))
				{
					$s .= $c;
					$c = '';
				}
				else $lastClass = $class;
			}

			$chars = implode('', $chars);
		}

		return $s . implode('', $a);
	}
}
