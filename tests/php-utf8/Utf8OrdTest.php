<?php

require_once PHP_UTF8_DIR.'/functions/ord.php';


class Utf8OrdTest extends PHPUnit_Framework_TestCase
{
	public function test_empty_str()
	{
		$str = '';
		$this->assertEquals(0, utf8\ord($str));
	}

	public function test_ascii_char()
	{
		$str = 'a';
		$this->assertEquals(97, utf8\ord($str));
	}

	public function test_2_byte_char()
	{
		$str = 'ñ';
		$this->assertEquals(241, utf8\ord($str));
	}

	public function test_3_byte_char()
	{
		$str = '₧';
		$this->assertEquals(8359, utf8\ord($str));
	}

	public function test_4_byte_char()
	{
		$str = "\xf0\x90\x8c\xbc";
		$this->assertEquals(66364, utf8\ord($str));
	}
}
