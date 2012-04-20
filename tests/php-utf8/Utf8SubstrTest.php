<?php

class Utf8SubstrTest extends PHPUnit_Framework_TestCase
{
	public function test_utf8()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals('Iñ', utf8\sub($str, 0, 2));
	}

	public function test_utf8_two()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals('të', utf8\sub($str, 2, 2));
	}

	public function test_utf8_zero()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals('Iñtërnâtiônàlizætiøn', utf8\sub($str, 0));
	}

	public function test_utf8_zero_zero()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals('', utf8\sub($str, 0, 0));
	}

	public function test_start_great_than_length()
	{
		$str = 'Iñt';
		$this->assertEmpty(utf8\sub($str, 4));
	}

	public function test_compare_start_great_than_length()
	{
		$str = 'abc';
		$this->assertEquals(substr($str, 4), utf8\sub($str, 4));
	}

	public function test_length_beyond_string()
	{
		$str = 'Iñt';
		$this->assertEquals('ñt', utf8\sub($str, 1, 5));
	}

	public function test_compare_length_beyond_string()
	{
		$str = 'abc';
		$this->assertEquals(substr($str, 1, 5), utf8\sub($str, 1, 5));
	}

	public function test_start_negative()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals('tiøn', utf8\sub($str, -4));
	}

	public function test_length_negative()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals('nàlizæti', utf8\sub($str, 10, -2));
	}

	public function test_start_length_negative()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals('ti', utf8\sub($str, -4, -2));
	}

	public function test_linefeed()
	{
		$str = "Iñ\ntërnâtiônàlizætiøn";
		$this->assertEquals("ñ\ntër", utf8\sub($str, 1, 5));
	}

	public function test_long_length()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals('Iñtërnâtiônàlizætiøn', utf8\sub($str, 0, 15536));
	}
}
