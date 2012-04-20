<?php

require_once PHP_UTF8_DIR.'/functions/strcspn.php';


class Utf8StrcspnTest extends PHPUnit_Framework_TestCase
{
	public function test_no_match_single_byte_search()
	{
		$str = 'iñtërnâtiônàlizætiøn';
		$this->assertEquals(2, utf8\cspn($str, 't'));
	}

	protected function tes_no_match_multi_byte_search()
	{
		$str = 'iñtërnâtiônàlizætiøn';
		$this->assertEquals(6, utf8\cspn($str, 'â'));
	}

	public function test_compare_strspn()
	{
		$str = 'aeioustr';
		$this->assertEquals(strcspn($str, 'tr'), utf8\cspn($str, 'tr'));
	}

	public function test_match_ascii()
	{
		$str = 'internationalization';
		$this->assertEquals(strcspn($str, 'a'), utf8\cspn($str, 'a'));
	}

	public function test_linefeed()
	{
		$str = "i\nñtërnâtiônàlizætiøn";
		$this->assertEquals(3, utf8\cspn($str, 't'));
	}

	public function test_linefeed_mask()
	{
		$str = "i\nñtërnâtiônàlizætiøn";
		$this->assertEquals(1, utf8\cspn($str, "\n"));
	}
}
