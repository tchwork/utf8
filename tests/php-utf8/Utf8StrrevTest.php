<?php

require_once PHP_UTF8_DIR.'/functions/strrev.php';


class Utf8StrrevTest extends PHPUnit_Framework_TestCase
{
	public function test_reverse()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$rev = 'nøitæzilànôitânrëtñI';
		$this->assertEquals($rev, utf8\reverse($str));
	}

	public function test_empty_str()
	{
		$str = '';
		$rev = '';
		$this->assertEquals($rev, utf8\reverse($str));
	}

	public function test_linefeed()
	{
		$str = "Iñtërnâtiôn\nàlizætiøn";
		$rev = "nøitæzilà\nnôitânrëtñI";
		$this->assertEquals($rev, utf8\reverse($str));
	}
}
