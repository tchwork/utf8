<?php

require_once PHP_UTF8_DIR.'/utils/ascii.php';


class Utf8IsAsciiTest extends PHPUnit_Framework_TestCase
{
	public function test_utf8()
	{
		$str = 'testiñg';
		$this->assertFalse(utf8\isAscii($str));
	}

	public function test_ascii()
	{
		$str = 'testing';
		$this->assertTrue(utf8\isAscii($str));
	}

	public function test_invalid_char()
	{
		$str = "tes\xe9ting";
		$this->assertFalse(utf8\isAscii($str));
	}

	public function test_empty_str()
	{
		$str = '';
		$this->assertTrue(utf8\isAscii($str));
	}
}
