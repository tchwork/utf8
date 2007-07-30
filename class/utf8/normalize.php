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

	$quickCheckNFC,
	$quickCheckNFD,
	$quickCheckNFKC,
	$quickCheckNFKD,

	$combiningCheck,

	$C,
	$D,
	$KD,

	$cC,

	$utf_len_mask = array("\xC0" => 2, "\xD0" => 2, "\xE0" => 3, "\xF0" => 4);


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
		return preg_replace_callback($K, array(__CLASS__, 'compose'), $s);
	}

	static function toNFD($s, $K = false)
	{
		self::$K = $K;
		$K ? ($K =& self::$quickCheckNFKD) : ($K =& self::$quickCheckNFD);
		return preg_replace_callback($K, array(__CLASS__, 'decompose'), $s);
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
		// Decompose

		$s = self::toNFD($s[0], self::$K);


		// Load decomposition tables

		isset(self::$C)  || self::$C  = unserialize(file_get_contents(resolvePath('data/utf8/canonicalComposition.ser')));
		isset(self::$cC) || self::$cC = unserialize(file_get_contents(resolvePath('data/utf8/combiningClass.ser')));


		// Compose

		$t = $tail = '';

		$i = $s[0] < "\x80" ? 1 : self::$utf_len_mask[$s[0] & "\xF0"];
		$len = strlen($s);

		$last_utf_chr = substr($s, 0, $i);
		$last_utf_cls = isset(self::$cC[$last_utf_chr]) ? 256 : 0;

		while ($i < $len)
		{
			$utf_len = $s[$i] < "\x80" ? 1 : self::$utf_len_mask[$s[$i] & "\xF0"];
			$utf_chr = substr($s, $i, $utf_len);

			if ($last_utf_cls
				||      $utf_chr < "\xe1\x85\xa1" || "\xe1\x85\xb5" < $utf_chr
				|| $last_utf_chr < "\xe1\x84\x80" || "\xe1\x84\x92" < $last_utf_chr)
			{
				// Tables lookup

				$utf_cls = isset(self::$cC[$utf_chr]) ? self::$cC[$utf_chr] : 0;

				if (isset(self::$C[$last_utf_chr . $utf_chr]) && (!$last_utf_cls || $last_utf_cls < $utf_cls))
				{
					$last_utf_chr = self::$C[$last_utf_chr . $utf_chr];
				}
				else if ($last_utf_cls = $utf_cls) $tail .= $utf_chr;
				else
				{
					$t .= $last_utf_chr . $tail;
					$tail = '';
					$last_utf_chr = $utf_chr;
				}
			}
			else
			{
				// Hangul chars

				$L = ord($last_utf_chr[2]) - 0x80;
				$V = ord($utf_chr[2]) - 0xa1;
				$T = 0;

				if ($i + $utf_len < $len && "\xe1" == $s[$i + $utf_len])
				{
					$utf_chr = substr($s, $i + $utf_len, 3);

					if ("\xe1\x86\xa7" <= $utf_chr && $utf_chr <= "\xe1\x87\x82")
					{
						$T = ord($utf_chr[2]) - 0xa7;
						0 > $T && $T += 0x40;
						$utf_len += 3;
					}
				}

				$L = 0xac00 + ($L * 21 + $V) * 28 + $T;
				$last_utf_chr = chr(0xe0 | $L>>12) . chr(0x80 | $L>>6 & 0x3f) . chr(0x80 | $L & 0x3f);
			}

			$i += $utf_len;
		}

		return $t . $last_utf_chr . $tail;
	}

	protected static function decompose($s)
	{
		$s = $s[0];


		// Load decomposition tables

		if (self::$K)
		{
			isset(self::$KD) || self::$KD = unserialize(file_get_contents(resolvePath('data/utf8/compatibilityDecomposition.ser')));
			$map =& self::$KD;
		}
		else
		{
			isset(self::$D)  || self::$D  = unserialize(file_get_contents(resolvePath('data/utf8/canonicalDecomposition.ser')));
			$map =& self::$D;
		}


		// Decompose

		$t = '';
		$i = 0;
		$len = strlen($s);

		while ($i < $len)
		{
			$utf_len = self::$utf_len_mask[$s[$i] & "\xF0"];
			$utf_chr = substr($s, $i, $utf_len);

			if ($utf_chr < "\xEA\xB0\x80" || "\xED\x9E\xA3" < $utf_chr)
			{
				// Table lookup

				isset($map[$utf_chr]) && $utf_chr = $map[$utf_chr];
			}
			else
			{
				// Hangul chars

				$utf_chr = unpack('C*', $utf_chr);
				$j = (($utf_chr[1]-224) << 12) + (($utf_chr[2]-128) << 6) + $utf_chr[3] - 0xac80;

				$utf_chr = "\xe1\x84" . chr(0x80 + (int)  ($j / 588))
				         . "\xe1\x85" . chr(0xa1 + (int) (($j % 588) / 28));

				if ($j %= 28)
				{
					$utf_chr .= $j < 25
						? ("\xe1\x86" . chr(0xa7 + $j))
						: ("\xe1\x87" . chr(0x67 + $j));
				}
			}

			$t .= $utf_chr;
			$i += $utf_len;
		}

		// Sort combining chars
		$t = preg_replace_callback('/[' . self::$combiningCheck . ']{2,}/u', array(__CLASS__, 'sortCombining'), $t);

		return $t;
	}

	protected static function sortCombining($s)
	{
		isset(self::$cC) || self::$cC = unserialize(file_get_contents(resolvePath('data/utf8/combiningClass.ser')));

		$s = $s[0];
		$a = array();
		$i = 0;
		$len = strlen($s);

		while ($i < $len)
		{
			$utf_len = self::$utf_len_mask[$s[$i] & "\xF0"];
			$utf_chr = substr($s, $i, $utf_len);

			isset($a[self::$cC[$utf_chr]]) || $a[self::$cC[$utf_chr]] = '';
			$a[self::$cC[$utf_chr]] .= $utf_chr;

			$i += $utf_len;
		}

		ksort($a);

		return implode('', $a);
	}
}
