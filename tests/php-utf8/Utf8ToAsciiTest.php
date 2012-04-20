<?php

require_once PHP_UTF8_DIR.'/utils/ascii.php';


class Utf8ToAsciiTest extends PHPUnit_Framework_TestCase
{
	public function test_utf8()
	{
		$str = 'testiñg';
		$this->assertEquals('testig', utf8\toAscii($str));
	}

	public function test_ascii()
	{
		$str = 'testing';
		$this->assertEquals('testing', utf8\toAscii($str));
	}

	public function test_invalid_char()
	{
		$str = "tes\xe9ting";
		$this->assertEquals('testing', utf8\toAscii($str));
	}

	public function test_empty_str()
	{
		$str = '';
		$this->assertEmpty(utf8\toAscii($str));
	}

	public function test_nul_and_non_7_bit()
	{
		$str = "a\x00ñ\x00c";
		$this->assertEquals('ac', utf8\toAscii($str, 'both'));
	}

	public function test_nul()
	{
		$str = "a\x00b\x00c";
		$this->assertEquals('abc', utf8\toAscii($str, 'ctrl_chars'));
	}
}
