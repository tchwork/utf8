<?php

class Utf8StrlenTest extends PHPUnit_Framework_TestCase
{
	public function test_utf8()
    {
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals(20, utf8\len($str));
    }

	public function test_utf8_invalid()
	{
		$str = "Iñtërnâtiôn\xe9àlizætiøn";
		$this->assertEquals(20, utf8\len($str));
	}

	public function test_ascii()
	{
		$str = 'ABC 123';
		$this->assertEquals(7, utf8\len($str));
	}

	public function test_empty_str()
	{
		$str = '';
		$this->assertEquals(0, utf8\len($str));
	}
}
