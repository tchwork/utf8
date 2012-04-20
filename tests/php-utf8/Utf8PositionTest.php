<?php

require_once PHP_UTF8_DIR.'/utils/position.php';


class Utf8PositionTest extends PHPUnit_Framework_TestCase
{
	public function test_ascii_char_to_byte()
	{
		$str = 'testing';
		$this->assertEquals(3, utf8\bytePosition($str, 3));
		$this->assertEquals(array(3, 4), utf8\bytePosition($str, 3, 4));
		$this->assertEquals(0, utf8\bytePosition($str, -1));
		$this->assertEquals(7, utf8\bytePosition($str, 8));
	}

	public function test_multibyte_char_to_byte()
	{
		$str = 'Iñtërnâtiônàlizætiøn';
		$this->assertEquals(4, utf8\bytePosition($str, 3));
		$this->assertEquals(array(4, 7), utf8\bytePosition($str, 3, 5));
		$this->assertEquals(0, utf8\bytePosition($str, -1));
		$this->assertEquals(27, utf8\bytePosition($str, 28));
	}

	// Tests for utf8\locateCurrentChr & utf8\locateNextChr
	public function test_singlebyte()
	{
		$tests   = array();

		// Single byte, should return current index
		$tests[] = array('aaживπά우리をあöä', 0, 0);
		$tests[] = array('aaживπά우리をあöä', 1, 1);

		foreach($tests as $test)
			$this->assertEquals($test[2], utf8\locateCurrentChr($test[0], $test[1]));

		$tests   = array();
		$tests[] = array('aaживπά우리をあöä', 1, 1);

		foreach($tests as $test)
			$this->assertEquals($test[2], utf8\locateNextChr($test[0], $test[1]));
	}

	public function test_two_byte()
	{
		// Two byte, should move to boundary, expect even number
		$tests   = array();
		$tests[] = array('aaживπά우리をあöä', 2, 2);
		$tests[] = array('aaживπά우리をあöä', 3, 2);
		$tests[] = array('aaживπά우리をあöä', 4, 4);

		foreach($tests as $test)
			$this->assertEquals($test[2], utf8\locateCurrentChr($test[0], $test[1]));

		$tests   = array();
		$tests[] = array('aaживπά우리をあöä', 2, 2);
		$tests[] = array('aaживπά우리をあöä', 3, 4);
		$tests[] = array('aaживπά우리をあöä', 4, 4);

		foreach($tests as $test)
			$this->assertEquals($test[2], utf8\locateNextChr($test[0], $test[1]));
	}

	public function test_threebyte()
	{
		// Three byte, should move to boundary 10 or 13
		$tests   = array();
		$tests[] = array('aaживπά우리をあöä', 10, 10);
		$tests[] = array('aaживπά우리をあöä', 11, 10);
		$tests[] = array('aaживπά우리をあöä', 12, 10);
		$tests[] = array('aaживπά우리をあöä', 13, 13);

		foreach($tests as $test)
			$this->assertEquals($test[2], utf8\locateCurrentChr($test[0], $test[1]));

		$tests   = array();
		$tests[] = array('aaживπά우리をあöä', 10, 10);
		$tests[] = array('aaживπά우리をあöä', 11, 13);
		$tests[] = array('aaживπά우리をあöä', 12, 13);
		$tests[] = array('aaживπά우리をあöä', 13, 13);

		foreach($tests as $test)
			$this->assertEquals($test[2], utf8\locateNextChr($test[0], $test[1]));
	}

	public function test_bounds()
	{
		// Bounds checking
		$tests   = array();
		$tests[] = array('aaживπά우리をあöä', -2, 0);
		$tests[] = array('aaживπά우리をあöä', 128, 29);

		foreach($tests as $test)
			$this->assertEquals($test[2], utf8\locateCurrentChr($test[0], $test[1]));

		$tests[] = array('aaживπά우리をあöä', -2, 0);
		$tests[] = array('aaживπά우리をあöä', 128, 29);

		foreach($tests as $test)
			$this->assertEquals($test[2], utf8\locateNextChr($test[0], $test[1]));
	}
}
