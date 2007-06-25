<?php /*********************************************************************
 *
 *   Copyright : (C) 2006 Nicolas Grekas. All rights reserved.
 *   Email     : nicolas.grekas+patchwork@espci.org
 *   License   : http://www.gnu.org/licenses/lgpl.txt GNU/LGPL, see LGPL
 *
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Lesser General Public
 *   License as published by the Free Software Foundation; either
 *   version 2.1 of the License, or (at your option) any later version.
 *
 ***************************************************************************/


class
{
	protected static $K;

	protected static $quickCheckNFC;
	protected static $quickCheckNFD;
	protected static $quickCheckNFKC;
	protected static $quickCheckNFKD;

	protected static $combiningCheck;

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
		self::$K = $K;
		$K ? ($K =& self::$quickCheckNFKC) : ($K =& self::$quickCheckNFC);
		$s = preg_replace_callback($K, array(__CLASS__, 'compose'), $s);

		return substr($s, 1, -1);
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


	// Some remaining ligatures for search engine decomposition

	static $lig = array(
		'Æ' => 'Ae', 'æ' => 'ae', 'ß' => 'ss', 'Œ' => 'Oe', 'œ' => 'oe', 'ʤ' => 'dz',
		'ʣ' => 'dz', 'ʥ' => 'dz', 'ƕ' => 'hv', 'Ƣ' => 'Oi', 'ƣ' => 'oi', 'ʨ' => 'tc',
		'ʦ' => 'ts', 'ƻ' => '2' , 'Ŋ' => 'Ng', 'ŋ' => 'ng',
	);

	static function toSearchString($s)
	{
		$s = self::toNFKD($s);
		$s = preg_replace_callback('/[' . self::$combiningCheck . ']+/', '', $s);
		$s = strtr($s, self::$lig);

		return $s;
	}


	protected static function compose($s)
	{
		static $map;
		isset($map) || $map = unserialize(file_get_contents(resolvePath('data/utf8/canonicalComposition.ser')));

		// Decompose
		$s = self::toNFD($s[0], self::$K);

		// Recompose
		$s = strtr($s, $map);

		// Compose Hangul chars
		$s = preg_replace_callback('/[\x{1100}-\x{1112}][\x{1161}-\x{1175}][\x{11a7}-\x{11C2}]/u', array(__CLASS__, 'composeHangul'), $s);

		return $s;
	}

	protected static function decompose($s)
	{
		// Decompose
		static $canonical;
		isset($canonical) || $canonical = unserialize(file_get_contents(resolvePath('data/utf8/canonicalDecomposition.ser')));
		$s = strtr($s[0], $canonical);

		if (self::$K)
		{
			static $compatibility;
			isset($compatibility) || $compatibility = unserialize(file_get_contents(resolvePath('data/utf8/compatibilityDecomposition.ser')));
			$s = strtr($s[0], $compatibility);
		}

		// Decompose Hangul chars
		$s = preg_replace_callback('/[\x{AC00}-\x{D7A3}]/u', array(__CLASS__, 'decomposeHangul'), $s);

		// Sort combining chars
		$s = preg_replace_callback('/[' . self::$combiningCheck . ']{2,}/u', array(__CLASS__, 'sortCombining'), $s);

		return $s;
	}


	const SBase = 0xAC00;

	const LBase = 0x1100;
	const VBase = 0x1161;
	const TBase = 0x11A7;

	const VCount = 21;
	const TCount = 28;
	const NCount = 588; // VCount x TCount

	protected static function decomposeHangul($s)
	{
		$i = u::ord($s[0]) - self::SBase;

		$l = (int)  ($i / self::NCount);
		$v = (int) (($i % self::NCount) / self::TCount);
		$t = $i % self::TCount;

		$s = "\xe1\x84" . chr(0x80 + $l) . "\xe1\x85" . chr(0xa1 + $v);
		$t && $s .= $t >= 25 ? ("\xe1\x87" . chr(0x80 + $t - 25)) : ("\xe1\x86" . chr(0xa7 + $t));

		return $s;
	}

	protected static function composeHangul($s)
	{
		$s = $s[0];

		$l = u::ord(substr($s, 0, 3)) - self::LBase;
		$v = u::ord(substr($s, 3, 3)) - self::VBase;
		$t = u::ord(substr($s, 6, 3)) - self::TBase;

		return u::chr(self::SBase + ($l * self::VCount + $v) * self::TCount + $t);
	}


	protected static function sortCombining($s)
	{
		static $combiningClass;
		isset($combiningClass) || $combiningClass = unserialize(file_get_contents(resolvePath('data/utf8/combiningClass.ser')));

		preg_match_all('/./u', $s[0], $s);

		$a = array();
		foreach ($s[0] as $s) $a[$s] = $combiningClass[$s];
		asort($a);

		return implode('', array_keys($a));
	}
}
