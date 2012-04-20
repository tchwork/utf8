<?php

require_once PHP_UTF8_DIR.'/utils/patterns.php'; // Is needed in native mode
require_once PHP_UTF8_DIR.'/utils/bad.php';


class Utf8BadFindTest extends PHPUnit_Framework_TestCase
{
	public function test_valid_utf8()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertFalse(utf8\badFind($str));
	}

	public function test_valid_utf8_ascii()
	{
		$str = 'testing';
		$this->assertFalse(utf8\badFind($str));
	}

	public function test_invalid_utf8()
	{
		$str = "Iñtërnâtiôn\xe9àlizætiøn";
		$this->assertEquals(15, utf8\badFind($str));
	}

	public function test_invalid_utf8_ascii()
	{
		$str = "this is an invalid char '\xe9' here";
		$this->assertEquals(25, utf8\badFind($str));
	}

	public function test_invalid_utf8_start()
	{
		$str = "\xe9Iñtërnâtiônàlizætiøn";
		$this->assertEquals(0, utf8\badFind($str));
	}

	public function test_invalid_utf8_end()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe9";
		$this->assertEquals(27, utf8\badFind($str));
	}

	public function test_valid_two_octet_id()
	{
		$str = "abc\xc3\xb1";
		$this->assertFalse(utf8\badFind($str));
	}

	public function test_invalid_two_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
		$this->assertEquals(28, utf8\badFind($str));
	}

	public function test_invalid_id_between_two_and_three()
	{
		$str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(27, utf8\badFind($str));
	}

	public function test_valid_three_octet_id()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\badFind($str));
	}

	public function test_invalid_three_octet_sequence_second()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(27, utf8\badFind($str));
	}

	public function test_invalid_three_octet_sequence_third()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
		$this->assertEquals(27, utf8\badFind($str));
	}

	public function test_valid_four_octet_id()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\badFind($str));
	}

	public function test_invalid_four_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
		$this->assertEquals(27, utf8\badFind($str));
	}

	public function test_invalid_five_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(27, utf8\badFind($str));
	}

	public function test_invalid_six_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(27, utf8\badFind($str));
	}

	public function test_valid_utf8_all()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertFalse(utf8\badFind($str, FALSE));
	}

	public function test_valid_utf8_ascii_all()
	{
		$str = 'testing';
		$this->assertFalse(utf8\badFind($str, FALSE));
	}

	public function test_invalid_utf8_all()
	{
		$str = "Iñtërnâtiôn\xe9àlizætiøn";
		$test = array(15);
		$this->assertEquals($test, utf8\badFind($str, FALSE));
	}

	public function test_invalid_utf8_ascii_all()
	{
		$str = "this is an invalid char '\xe9' here";
		$test = array(25);
		$this->assertEquals($test, utf8\badFind($str, FALSE));
	}

	public function test_invalid_utf8_multiple_all()
	{
		$str = "\xe9Iñtërnâtiôn\xe9àlizætiøn\xe9";
		$test = array(0, 16, 29);
		$this->assertEquals($test, utf8\badFind($str, FALSE));
	}

	public function test_valid_two_octet_id_all()
	{
		$str = "abc\xc3\xb1";
		$this->assertFalse(utf8\badFind($str, FALSE));
	}

	public function test_invalid_two_octet_sequence_all()
	{
		$str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
		$this->assertEquals(array(28), utf8\badFind($str, FALSE));
	}

	public function test_invalid_id_between_two_and_three_all()
	{
		$str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(array(27, 28), utf8\badFind($str, FALSE));
	}

	public function test_valid_three_octet_id_all()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\badFind($str, FALSE));
	}

	public function test_invalid_three_octet_sequence_second_all()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(array(27, 29), utf8\badFind($str, FALSE));
	}

	public function test_invalid_three_octet_sequence_third_all()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
		$this->assertEquals(27, utf8\badFind($str));
	}

	public function test_valid_four_octet_id_all()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\badFind($str, FALSE));
	}

	public function test_invalid_four_octet_sequence_all()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
		$this->assertEquals(array(27, 29, 30), utf8\badFind($str, FALSE));
	}

	public function test_invalid_five_octet_sequence_all()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(range(27, 31), utf8\badFind($str, FALSE));
	}

	public function test_invalid_six_octet_sequence_all()
	{
		$str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(range(27, 32), utf8\badFind($str, FALSE));
	}
}
