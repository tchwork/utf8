<?php

require_once PHP_UTF8_DIR.'/utils/unicode.php';
require_once PHP_UTF8_DIR.'/utils/specials.php';


class Utf8IsWordCharsTest extends PHPUnit_Framework_TestCase
{
	public function test_empty_string()
	{
		$this->assertTrue(utf8\isWordChars(''));
	}

	public function test_all_word_chars()
	{
		$this->assertTrue(utf8\isWordChars('HelloWorld'));
	}

	public function test_specials()
	{
		$str = 'Hello '.
			chr(0xe0 | (0x2234 >> 12)).
			chr(0x80 | ((0x2234 >> 6) & 0x003f)).
			chr(0x80 | (0x2234 & 0x003f)).
			' World';

		$this->assertFalse(utf8\isWordChars($str));
	}
}
