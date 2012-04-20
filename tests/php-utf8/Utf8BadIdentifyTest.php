<?php

require_once PHP_UTF8_DIR.'/utils/patterns.php'; // Is needed in native mode
require_once PHP_UTF8_DIR.'/utils/bad.php';


class Utf8BadIdentifyTest extends PHPUnit_Framework_TestCase
{
	public function test_valid_utf8()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertFalse(utf8\badIdentify($str, $i));
		$this->assertNull($i);
	}

	public function test_valid_utf8_ascii()
	{
		$str = 'testing';
		$this->assertFalse(utf8\badIdentify($str, $i));
		$this->assertNull($i);
	}

	public function test_invalid_utf8()
	{
		$str = "Iñtërnâtiôn\xe9àlizætiøn";
		$this->assertEquals(utf8\BAD_SEQINCOMPLETE, utf8\badIdentify($str, $i));
		$this->assertEquals(15, $i);
	}

	public function test_invalid_utf8_ascii()
	{
		$str = "this is an invalid char '\xe9' here";
		$this->assertEquals(utf8\BAD_SEQINCOMPLETE, utf8\badIdentify($str, $i));
		$this->assertEquals(25, $i);
	}

	public function test_invalid_utf8_start()
	{
		$str = "\xe9Iñtërnâtiônàlizætiøn";
		$this->assertEquals(utf8\BAD_SEQINCOMPLETE, utf8\badIdentify($str, $i));
		$this->assertEquals(0, $i);
	}

	public function test_invalid_utf8_end()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe9";
		$this->assertEquals(utf8\BAD_SEQINCOMPLETE, utf8\badIdentify($str, $i));
		$this->assertEquals(27, $i);
	}

	public function test_valid_two_octet_id()
	{
		$str = "abc\xc3\xb1";
		$this->assertFalse(utf8\badIdentify($str, $i));
		$this->assertNull($i);
	}

	public function test_invalid_two_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
		$this->assertEquals(utf8\BAD_SEQINCOMPLETE, utf8\badIdentify($str, $i));
		$this->assertEquals(28, $i);
	}

	public function test_invalid_id_between_two_and_three()
	{
		$str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(utf8\BAD_SEQID, utf8\badIdentify($str, $i));
		$this->assertEquals(27, $i);
	}

	public function test_valid_three_octet_id()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\badIdentify($str, $i));
		$this->assertNull($i);
	}

	public function test_invalid_three_octet_sequence_second()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(utf8\BAD_SEQINCOMPLETE, utf8\badIdentify($str, $i));
		$this->assertEquals(27, $i);
	}

	public function test_invalid_three_octet_sequence_third()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
		$this->assertEquals(utf8\BAD_SEQINCOMPLETE, utf8\badIdentify($str, $i));
		$this->assertEquals(28, $i);
	}

	public function test_valid_four_octet_id()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
		$this->assertFalse(utf8\badIdentify($str, $i));
		$this->assertNull($i);
	}

	public function test_invalid_four_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
		$this->assertEquals(utf8\BAD_SEQINCOMPLETE, utf8\badIdentify($str, $i));
		$this->assertEquals(27, $i);
	}

	public function test_invalid_five_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(utf8\BAD_5OCTET, utf8\badIdentify($str, $i));
		$this->assertEquals(27, $i);
	}

	public function test_invalid_six_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals(utf8\BAD_6OCTET, utf8\badIdentify($str, $i));
		$this->assertEquals(27, $i);
	}
}
