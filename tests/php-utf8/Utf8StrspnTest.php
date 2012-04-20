<?php

require_once PHP_UTF8_DIR.'/functions/strspn.php';


class Utf8StrspnTest extends PHPUnit_Framework_TestCase
{
	public function test_match()
	{
		$str = 'iñtërnâtiônàlizætiøn';
		$this->assertEquals(11, utf8\span($str, 'âëiônñrt'));
	}

	public function test_match_two()
	{
		$str = 'iñtërnâtiônàlizætiøn';
		$this->assertEquals(4, utf8\span($str, 'iñtë'));
	}

	public function test_compare_strspn()
	{
		$str = 'aeioustr';
		$this->assertEquals(strspn($str, 'saeiou'), utf8\span($str, 'saeiou'));
	}

	public function test_match_ascii()
	{
		$str = 'internationalization';
		$this->assertEquals(strspn($str, 'aeionrt'), utf8\span($str, 'aeionrt'));
	}

	public function test_linefeed()
	{
		$str = "iñtërnât\niônàlizætiøn";
		$this->assertEquals(8, utf8\span($str, 'âëiônñrt'));
	}

	public function test_linefeed_mask()
	{
		$str = "iñtërnât\niônàlizætiøn";
		$this->assertEquals(12, utf8\span($str, "âëiônñrt\n"));
	}
}
