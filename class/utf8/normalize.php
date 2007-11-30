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


// See http://www.unicode.org/reports/tr15/


#>>> Add compatibility with non patchwork code
utf8_normalizer::__constructStatic();
#<<<

class utf8_normalizer
{
	// ASCII characters, by frequency
	const ASCII = "\x20\x65\x69\x61\x73\x6e\x74\x72\x6f\x6c\x75\x64\x5d\x5b\x63\x6d\x70\x27\x0a\x67\x7c\x68\x76\x2e\x66\x62\x2c\x3a\x3d\x2d\x71\x31\x30\x43\x32\x2a\x79\x78\x29\x28\x4c\x39\x41\x53\x2f\x50\x22\x45\x6a\x4d\x49\x6b\x33\x3e\x35\x54\x3c\x44\x34\x7d\x42\x7b\x38\x46\x77\x52\x36\x37\x55\x47\x4e\x3b\x4a\x7a\x56\x23\x48\x4f\x57\x5f\x26\x21\x4b\x3f\x58\x51\x25\x59\x5c\x09\x5a\x2b\x7e\x5e\x24\x40\x60\x7f\x00\x01\x02\x03\x04\x05\x06\x07\x08\x0b\x0c\x0d\x0e\x0f\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f";


	static

	// Some remaining chars for accents decomposition
	// from http://www.unicode.org/cldr/
	$lig = array(
		'Æ' => 'AE', 'æ' => 'ae', 'ß' => 'ss', 'Œ' => 'OE', 'œ' => 'oe', 'ʤ' => 'dz',
		'ʣ' => 'dz', 'ʥ' => 'dz', 'ƕ' => 'hv', 'Ƣ' => 'OI', 'ƣ' => 'oi', 'ʨ' => 'tc',
		'ʦ' => 'ts', 'ƻ' => '2' , 'Ŋ' => 'NG', 'ŋ' => 'ng', 'Ð' => 'D' , 'ð' => 'd' ,
		'Ø' => 'O' , 'ø' => 'o' , 'Þ' => 'TH', 'þ' => 'th', 'Θ' => 'T' , 'θ' => 'T' ,
		'Ʃ' => 'SH', 'ʃ' => 'sh', 'Ʒ' => 'ZH', 'ʒ' => 'zh', 'Ʊ' => 'U' , 'ʊ' => 'u' ,
		'Ə' => 'A' , 'ə' => 'a' , 'Ɔ' => 'O' , 'ɔ' => 'o' , 'Ɛ' => 'E' , 'ɛ' => 'e' ,
		'ʔ' => '?' , 'ɪ' => 'i' , 'ʌ' => 'v',
	);


	protected static

	$C, $D, $KD, $cC, $K,
	$utf_len_mask = array("\xC0" => 2, "\xD0" => 2, "\xE0" => 3, "\xF0" => 4);


	static function toNFC($s)  {return self::normalize($s, true , false);}
	static function toNFD($s)  {return self::normalize($s, false, false);}
	static function toNFKC($s) {return self::normalize($s, true , true );}
	static function toNFKD($s) {return self::normalize($s, false, true );}

	static function removeAccents($s)
	{
		$s = self::toNFKD($s);
		$s = preg_replace('/\Mn+/u', '', $s);
		$s = strtr($s, self::$lig);

		return self::recompose($s, false);
	}


	static function __constructStatic()
	{
		self::$C  = unserialize(file_get_contents(resolvePath('data/utf8/canonicalComposition.ser')));
		self::$D  = unserialize(file_get_contents(resolvePath('data/utf8/canonicalDecomposition.ser')));
		self::$cC = unserialize(file_get_contents(resolvePath('data/utf8/combiningClass.ser')));
	}


	protected static function normalize($s, $C, $K)
	{
		if ($K) isset(self::$KD) || self::$KD = unserialize(file_get_contents(resolvePath('data/utf8/compatibilityDecomposition.ser')));
		self::$K = $K;

		return $C ? self::recompose($s) : self::decompose($s);
	}

	protected static function recompose($s, $decompose = true)
	{
		$decompose && $s = self::decompose($s);

		ob_start();

		$tail = '';

		$i = $s[0] < "\x80" ? 1 : self::$utf_len_mask[$s[0] & "\xF0"];
		$len = strlen($s);

		$last_utf_chr = substr($s, 0, $i);
		$last_utf_cls = isset(self::$cC[$last_utf_chr]) ? 256 : 0;

		while ($i < $len)
		{
			if ($s[$i] < "\x80")
			{
				// ASCII chars

				if ($tail)
				{
					$last_utf_chr .= $tail;
					$tail = '';
				}

				if ($j = strspn($s, self::ASCII, $i+1))
				{
					$last_utf_chr .= substr($s, $i, $j);
					$i += $j;
				}

				echo $last_utf_chr;
				$last_utf_chr = $s[$i];
				++$i;
			}
			else
			{
				$utf_len = self::$utf_len_mask[$s[$i] & "\xF0"];
				$utf_chr = substr($s, $i, $utf_len);

				if ($last_utf_chr < "\xe1\x84\x80" || "\xe1\x84\x92" < $last_utf_chr
				    ||   $utf_chr < "\xe1\x85\xa1" || "\xe1\x85\xb5" < $utf_chr
				    || $last_utf_cls)
				{
					// Table lookup and combining chars composition

					$utf_cls = isset(self::$cC[$utf_chr]) ? self::$cC[$utf_chr] : 0;

					if (isset(self::$C[$last_utf_chr . $utf_chr]) && (!$last_utf_cls || $last_utf_cls < $utf_cls))
					{
						$last_utf_chr = self::$C[$last_utf_chr . $utf_chr];
					}
					else if ($last_utf_cls = $utf_cls) $tail .= $utf_chr;
					else
					{
						if ($tail)
						{
							$last_utf_chr .= $tail;
							$tail = '';
						}

						echo $last_utf_chr;
						$last_utf_chr = $utf_chr;
					}
				}
				else
				{
					// Hangul chars

					$L = ord($last_utf_chr[2]) - 0x80;
					$V = ord($utf_chr[2]) - 0xa1;
					$T = 0;

					$utf_chr = substr($s, $i + $utf_len, 3);

					if ("\xe1\x86\xa7" <= $utf_chr && $utf_chr <= "\xe1\x87\x82")
					{
						$T = ord($utf_chr[2]) - 0xa7;
						0 > $T && $T += 0x40;
						$utf_len += 3;
					}

					$L = 0xac00 + ($L * 21 + $V) * 28 + $T;
					$last_utf_chr = chr(0xe0 | $L>>12) . chr(0x80 | $L>>6 & 0x3f) . chr(0x80 | $L & 0x3f);
				}

				$i += $utf_len;
			}
		}

		echo $last_utf_chr, $tail;

		return ob_get_clean();
	}

	protected static function decompose($s)
	{
		ob_start();

		$c = array();
		$i = 0;
		$len = strlen($s);

		while ($i < $len)
		{
			if ($s[$i] < "\x80")
			{
				// ASCII chars

				if ($c)
				{
					ksort($c);
					echo implode('', $c);
					$c = array();
				}

				$j = 1 + strspn($s, self::ASCII, $i+1);
				echo substr($s, $i, $j);
				$i += $j;
			}
			else
			{
				$utf_len = self::$utf_len_mask[$s[$i] & "\xf0"];
				$utf_chr = substr($s, $i, $utf_len);
				$i += $utf_len;

				if (isset(self::$cC[$utf_chr]))
				{
					// Combining chars, for sorting

					isset($c[self::$cC[$utf_chr]]) || $c[self::$cC[$utf_chr]] = '';
					$c[self::$cC[$utf_chr]] .= self::$K && isset(self::$KD[$utf_chr]) ? self::$KD[$utf_chr] : (isset(self::$D[$utf_chr]) ? self::$D[$utf_chr] : $utf_chr);
				}
				else
				{
					if ($c)
					{
						ksort($c);
						echo implode('', $c);
						$c = array();
					}

					if ($utf_chr < "\xea\xb0\x80" || "\xed\x9e\xa3" < $utf_chr)
					{
						// Table lookup

						$j = self::$K && isset(self::$KD[$utf_chr]) ? self::$KD[$utf_chr] : (isset(self::$D[$utf_chr]) ? self::$D[$utf_chr] : $utf_chr);

						if ($utf_chr != $j)
						{
							$utf_chr = $j;

							$j = strlen($utf_chr);
							$utf_len = $utf_chr[0] < "\x80" ? 1 : self::$utf_len_mask[$utf_chr[0] & "\xf0"];

							if ($utf_len != $j)
							{
								// Put trailing chars in $s

								$j -= $utf_len;
								$i -= $j;

								if (0 > $i)
								{
									$s = str_repeat(' ', -$i) . $s;
									$len -= $i;
									$i = 0;
								}

								while ($j--) $s[$i+$j] = $utf_chr[$utf_len+$j];

								$utf_chr = substr($utf_chr, 0, $utf_len);
							}
						}
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

					echo $utf_chr;
				}
			}
		}

		if ($c)
		{
			ksort($c);
			echo implode('', $c);
		}

		return ob_get_clean();
	}
}
