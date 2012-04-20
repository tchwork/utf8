<?php

require_once PHP_UTF8_DIR.'/utils/unicode.php';
require_once PHP_UTF8_DIR.'/utils/specials.php';


class Utf8StripSpecialsTest extends PHPUnit_Framework_TestCase
{
	public function test_empty_string()
	{
		$this->assertEquals('', utf8\stripSpecials(''));
	}

	public function test_strip()
	{
		$str = 'Hello '.
			chr(0xe0 | (0x2234 >> 12)).
			chr(0x80 | ((0x2234 >> 6) & 0x003f)).
			chr(0x80 | (0x2234 & 0x003f)).
			' World';

		$this->assertEquals('HelloWorld', utf8\stripSpecials($str));
	}
}
