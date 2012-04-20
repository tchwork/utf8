<?php

class Utf8StrposTest extends PHPUnit_Framework_TestCase
{
	public function test_utf8()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals(6, utf8\pos($str, 'â'));
	}

	public function test_utf8_offset()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals(19, utf8\pos($str, 'n', 11));
	}

	public function test_utf8_invalid()
	{
		$str = "Iñtërnâtiôn\xe9àlizætiøn";
		$this->assertEquals(15, utf8\pos($str, 'æ'));
	}

	public function test_ascii()
	{
		$str = 'ABC 123';
		$this->assertEquals(1, utf8\pos($str, 'B'));
	}

	public function test_vs_strpos()
	{
		$str = 'ABC 123 ABC';
		$this->assertEquals(strpos($str, 'B', 3), utf8\pos($str, 'B', 3));
	}

	public function test_empty_str()
	{
		$str = '';
		$this->assertFalse(utf8\pos($str, 'x'));
	}
}
