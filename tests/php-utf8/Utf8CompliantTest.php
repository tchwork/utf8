<?php

require_once PHP_UTF8_DIR.'/utils/validation.php';


class Utf8CompliantTest extends PHPUnit_Framework_TestCase
{
	public function test_valid_utf8()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertTrue(utf8\compliant($str));
	}

	public function test_valid_utf8_ascii()
	{
		$str = 'ABC 123';
		$this->assertTrue(utf8\compliant($str));
	}

	public function test_invalid_utf8()
	{
		$str = "Iñtërnâtiôn\xe9àlizætiøn";
		$this->assertFalse(utf8\compliant($str));
	}

	public function test_invalid_utf8_ascii()
	{
		$str = "this is an invalid char '\xe9' here";
		$this->assertFalse(utf8\compliant($str));
	}

	public function test_empty_string()
	{
		$str = '';
		$this->assertTrue(utf8\compliant($str));
	}

	public function test_valid_two_octet_id()
	{
		$str = "\xc3\xb1";
		$this->assertTrue(utf8\compliant($str));
	}

	public function test_invalid_two_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\compliant($str));
	}

	public function test_invalid_id_between_twoAnd_three()
	{
		$str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\compliant($str));
	}

	public function test_valid_three_octet_id()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
		$this->assertTrue(utf8\compliant($str));
	}

	public function test_invalid_three_octet_sequence_second()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\compliant($str));
	}

	public function test_invalid_three_octet_sequence_third()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\compliant($str));
	}

	public function test_valid_four_octet_id()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
		$this->assertTrue(utf8\compliant($str));
	}

	public function test_invalid_four_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\compliant($str));
	}

	public function test_invalid_five_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\compliant($str));
	}

	public function test_invalid_six_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\compliant($str));
	}
}
