<?php

require_once PHP_UTF8_DIR.'/functions/ucfirst.php';


class Utf8UcfirstTest extends PHPUnit_Framework_TestCase
{
	public function test_ucfirst()
	{
		$str = 'ñtërnâtiônàlizætiøn';
		$ucfirst = 'Ñtërnâtiônàlizætiøn';
		$this->assertEquals($ucfirst, utf8\ucfirst($str));
	}

	public function test_ucfirst_space()
	{
		$str = ' iñtërnâtiônàlizætiøn';
		$ucfirst = ' iñtërnâtiônàlizætiøn';
		$this->assertEquals($ucfirst, utf8\ucfirst($str));
	}

	public function test_ucfirst_upper()
	{
		$str = 'Ñtërnâtiônàlizætiøn';
		$ucfirst = 'Ñtërnâtiônàlizætiøn';
		$this->assertEquals($ucfirst, utf8\ucfirst($str));
	}

	public function test_empty_string()
	{
		$str = '';
		$this->assertEquals('', utf8\ucfirst($str));
	}

	public function test_one_char()
	{
		$str = 'ñ';
		$ucfirst = "Ñ";
		$this->assertEquals($ucfirst, utf8\ucfirst($str));
	}

	public function test_linefeed()
	{
		$str = "ñtërn\nâtiônàlizætiøn";
		$ucfirst = "Ñtërn\nâtiônàlizætiøn";
		$this->assertEquals($ucfirst, utf8\ucfirst($str));
	}
}
