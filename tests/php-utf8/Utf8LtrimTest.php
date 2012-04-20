<?php

require_once PHP_UTF8_DIR.'/functions/trim.php';


class Utf8LtrimTest extends PHPUnit_Framework_TestCase
{
	public function test_trim()
	{
		$str = 'ñtërnâtiônàlizætiøn';
		$trimmed = 'tërnâtiônàlizætiøn';
		$this->assertEquals($trimmed, utf8\ltrim($str, 'ñ'));
	}

	public function test_no_trim()
	{
		$str = ' Iñtërnâtiônàlizætiøn';
		$trimmed = ' Iñtërnâtiônàlizætiøn';
		$this->assertEquals($trimmed, utf8\ltrim($str, 'ñ'));
	}

	public function test_empty_string()
	{
		$str = '';
		$trimmed = '';
		$this->assertEquals($trimmed, utf8\ltrim($str));
	}

	public function test_forward_slash()
	{
		$str = '/Iñtërnâtiônàlizætiøn';
		$trimmed = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals($trimmed, utf8\ltrim($str, '/'));
	}

	public function test_negate_char_class()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$trimmed = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals($trimmed, utf8\ltrim($str, '^s'));
	}

	public function test_linefeed()
	{
		$str = "ñ\nñtërnâtiônàlizætiøn";
		$trimmed = "\nñtërnâtiônàlizætiøn";
		$this->assertEquals($trimmed, utf8\ltrim($str, 'ñ'));
	}

	public function test_linefeed_mask()
	{
		$str = "ñ\nñtërnâtiônàlizætiøn";
		$trimmed = "tërnâtiônàlizætiøn";
		$this->assertEquals($trimmed, utf8\ltrim($str, "ñ\n"));
	}
}
