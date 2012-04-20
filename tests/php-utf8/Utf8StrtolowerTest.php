<?php

class Utf8StrtolowerTest extends PHPUnit_Framework_TestCase
{
	public function test_lower()
	{
		$str = 'IÑTËRNÂTIÔNÀLIZÆTIØN';
		$lower = 'iñtërnâtiônàlizætiøn';
		$this->assertEquals($lower, utf8\toLower($str));
	}

	public function test_empty_string()
	{
		$str = '';
		$lower = '';
		$this->assertEquals($lower, utf8\toLower($str));
	}
}
