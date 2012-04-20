<?php

require_once PHP_UTF8_DIR.'/functions/substr_replace.php';


class Utf8SubstrReplaceTest extends PHPUnit_Framework_TestCase
{
	public function test_replace_start()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$replaced = 'IñtërnâtX';
		$this->assertEquals($replaced, utf8\subReplace($str, 'X', 8));
	}

	public function test_empty_string()
	{
		$str = '';
		$replaced = 'X';
		$this->assertEquals($replaced, utf8\subReplace($str, 'X', 8));
	}

	public function test_negative()
	{
		$str = 'testing';
		$replaced = substr_replace($str, 'foo', -2, -2);
		$this->assertEquals($replaced, utf8\subReplace($str, 'foo', -2, -2));
	}

	public function test_zero()
	{
		$str = 'testing';
		$replaced = substr_replace($str, 'foo', 0, 0);
		$this->assertEquals($replaced, utf8\subReplace($str, 'foo', 0, 0));
	}

	public function test_linefeed()
	{
		$str = "Iñ\ntërnâtiônàlizætiøn";
		$replaced = "Iñ\ntërnâtX";
		$this->assertEquals($replaced, utf8\subReplace($str, 'X', 9));
	}

	public function test_linefeed_replace()
	{
		$str = "Iñ\ntërnâtiônàlizætiøn";
		$replaced = "Iñ\ntërnâtX\nY";
		$this->assertEquals($replaced, utf8\subReplace($str, "X\nY", 9));
	}
}
