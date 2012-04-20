<?php

require_once PHP_UTF8_DIR.'/functions/stristr.php';


class Utf8StristrTest extends PHPUnit_Framework_TestCase
{
	public function test_substr()
	{
		$str = 'iñtërnâtiônàlizætiøn';
		$search = 'NÂT';
		$this->assertEquals('nâtiônàlizætiøn', utf8\ifind($str, $search));
	}

	public function test_substr_no_match()
	{
		$str = 'iñtërnâtiônàlizætiøn';
		$search = 'foo';
		$this->assertFalse(utf8\ifind($str, $search));
	}

	public function test_empty_search()
	{
		$str = 'iñtërnâtiônàlizætiøn';
		$search = '';
		$this->assertEquals('iñtërnâtiônàlizætiøn', utf8\ifind($str, $search));
	}

	public function test_empty_str()
	{
		$str = '';
		$search = 'NÂT';
		$this->assertFalse(utf8\ifind($str, $search));
	}

	public function test_empty_both()
	{
		$str = '';
		$search = '';
		$this->assertEmpty(utf8\ifind($str, $search));
	}

	public function test_linefeed_str()
	{
		$str = "iñt\nërnâtiônàlizætiøn";
		$search = 'NÂT';
		$this->assertEquals('nâtiônàlizætiøn', utf8\ifind($str, $search));
	}

	public function test_linefeed_both()
	{
		$str = "iñtërn\nâtiônàlizætiøn";
		$search = "N\nÂT";
		$this->assertEquals("n\nâtiônàlizætiøn", utf8\ifind($str, $search));
	}
}
