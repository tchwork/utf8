<?php

require_once PHP_UTF8_DIR.'/utils/patterns.php'; // Is needed in native mode
require_once PHP_UTF8_DIR.'/utils/bad.php';


class Utf8BadCleanTest extends PHPUnit_Framework_TestCase
{
	public function test_valid_utf8()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals($str, utf8\badClean($str));
	}

	public function test_valid_utf8_ascii()
	{
		$str = 'testing';
		$this->assertEquals($str, utf8\badClean($str));
	}

	public function test_invalid_utf8()
	{
		$str = "Iñtërnâtiôn\xe9àlizætiøn";
		$this->assertEquals('Iñtërnâtiônàlizætiøn', utf8\badClean($str));
	}

	public function test_invalid_utf8_ascii()
	{
		$str = "this is an invalid char '\xe9' here";
		$this->assertEquals("this is an invalid char '' here", utf8\badClean($str));
	}

	public function test_invalid_utf8_multiple()
	{
		$str = "\xe9Iñtërnâtiôn\xe9àlizætiøn\xe9";
		$this->assertEquals('Iñtërnâtiônàlizætiøn', utf8\badClean($str));
	}

	public function test_valid_two_octet_id()
	{
		$str = "abc\xc3\xb1";
		$this->assertEquals($str, utf8\badClean($str));
	}

	public function test_invalid_two_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
		$stripped = "Iñtërnâtiônàlizætiøn \x28 Iñtërnâtiônàlizætiøn";
		$this->assertEquals($stripped, utf8\badClean($str));
	}

	public function test_invalid_id_between_two_and_three()
	{
		$str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
		$stripped = "IñtërnâtiônàlizætiønIñtërnâtiônàlizætiøn";
		$this->assertEquals($stripped, utf8\badClean($str));
	}

	public function test_valid_three_octet_id()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals($str, utf8\badClean($str));
	}

	public function test_invalid_three_octet_sequence_second()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
		$stripped = "Iñtërnâtiônàlizætiøn(Iñtërnâtiônàlizætiøn";
		$this->assertEquals($stripped, utf8\badClean($str));
	}

	public function test_invalid_three_octet_sequence_third()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
		$stripped = "Iñtërnâtiônàlizætiøn(Iñtërnâtiônàlizætiøn";
		$this->assertEquals($stripped, utf8\badClean($str));
	}

	public function test_valid_four_octet_id()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
		$this->assertEquals($str, utf8\badClean($str));
	}

	public function test_invalid_four_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
		$stripped = "Iñtërnâtiônàlizætiøn(Iñtërnâtiônàlizætiøn";
		$this->assertEquals($stripped, utf8\badClean($str));
	}

	public function test_invalid_five_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$stripped = "IñtërnâtiônàlizætiønIñtërnâtiônàlizætiøn";
		$this->assertEquals($stripped, utf8\badClean($str));
	}

	public function test_invalid_six_octet_sequence()
	{
		$str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$stripped = "IñtërnâtiônàlizætiønIñtërnâtiônàlizætiøn";
		$this->assertEquals($stripped, utf8\badClean($str));
	}

	public function test_valid_utf8_replace()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals($str, utf8\badClean($str, '?'));
	}

	public function test_valid_utf8_ascii_replace()
	{
		$str = 'testing';
		$this->assertEquals($str, utf8\badClean($str, '?'));
	}

	public function test_invalid_utf8_replace()
	{
		$str = "Iñtërnâtiôn\xe9àlizætiøn";
		$this->assertEquals('Iñtërnâtiôn?àlizætiøn', utf8\badClean($str, '?'));
	}

	public function test_invalid_utf8_with_x_replace()
	{
		$str = "Iñtërnâtiôn\xe9àlizætiøn";
		$this->assertEquals('IñtërnâtiônXàlizætiøn', utf8\badClean($str, 'X'));
	}

	public function test_invalid_utf8_ascii_replace()
	{
		$str = "this is an invalid char '\xe9' here";
		$this->assertEquals("this is an invalid char '?' here", utf8\badClean($str, '?'));
	}

	public function test_invalid_utf8_multiple_replace()
	{
		$str = "\xe9Iñtërnâtiôn\xe9àlizætiøn\xe9";
		$this->assertEquals('?Iñtërnâtiôn?àlizætiøn?', utf8\badClean($str, '?'));
	}

	public function test_valid_two_octet_id_replace()
	{
		$str = "abc\xc3\xb1";
		$this->assertEquals($str, utf8\badClean($str, '?'));
	}

	public function test_invalid_two_octet_sequence_replace()
	{
		$str = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
		$replaced = "Iñtërnâtiônàlizætiøn ?( Iñtërnâtiônàlizætiøn";
		$this->assertEquals($replaced, utf8\badClean($str, '?'));
	}

	public function test_invalid_id_between_two_and_three_replace()
	{
		$str = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
		$replaced = "Iñtërnâtiônàlizætiøn??Iñtërnâtiônàlizætiøn";
		$this->assertEquals($replaced, utf8\badClean($str, '?'));
	}

	public function test_valid_three_octet_id_replace()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
		$this->assertEquals($str, utf8\badClean($str, '?'));
	}

	public function test_invalid_three_octet_sequence_second_replace()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
		$replaced = "Iñtërnâtiônàlizætiøn?(?Iñtërnâtiônàlizætiøn";
		$this->assertEquals($replaced, utf8\badClean($str, '?'));
	}

	public function test_invalid_three_octet_sequence_third_replace()
	{
		$str = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
		$replaced = "Iñtërnâtiônàlizætiøn??(Iñtërnâtiônàlizætiøn";
		$this->assertEquals($replaced, utf8\badClean($str, '?'));
	}

	public function test_valid_four_octet_id_replace()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
		$this->assertEquals($str, utf8\badClean($str, '?'));
	}

	public function test_invalid_four_octet_sequence_replace()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
		$replaced = "Iñtërnâtiônàlizætiøn?(??Iñtërnâtiônàlizætiøn";
		$this->assertEquals($replaced, utf8\badClean($str, '?'));
	}

	public function test_invalid_five_octet_sequence_replace()
	{
		$str = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$replaced = "Iñtërnâtiônàlizætiøn?????Iñtërnâtiônàlizætiøn";
		$this->assertEquals($replaced, utf8\badClean($str, '?'));
	}

	public function test_invalid_six_octet_sequence_replace()
	{
		$str = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
		$replaced = "Iñtërnâtiônàlizætiøn??????Iñtërnâtiônàlizætiøn";
		$this->assertEquals($replaced, utf8\badClean($str, '?'));
	}
}
