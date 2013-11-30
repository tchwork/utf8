<?php

namespace Patchwork\Tests\Utf8;

/**
 * @covers Patchwork\Utf8\WinFsStreamWrapper::<!public>
 */
class WinFsStreamWrapperTest extends \PHPUnit_Framework_TestCase
{
    protected static $dir;

    static function setUpBeforeClass()
    {
        self::$dir = 'win://' . __DIR__ . '/../µ€';
        stream_wrapper_register('win', 'Patchwork\Utf8\WinFsStreamWrapper');
        extension_loaded('com_dotnet') and mkdir(self::$dir);
    }

    static function  tearDownAfterClass()
    {
        extension_loaded('com_dotnet') and rmdir(self::$dir);
        stream_wrapper_unregister('win');
    }

    function setUp()
    {
        if (! extension_loaded('com_dotnet')) $this->markTestSkipped('Extension com_dotnet is required.');
    }

    /**
     * @covers Patchwork\Utf8\WinFsStreamWrapper::dir_opendir
     * @covers Patchwork\Utf8\WinFsStreamWrapper::dir_readdir
     * @covers Patchwork\Utf8\WinFsStreamWrapper::dir_rewinddir
     * @covers Patchwork\Utf8\WinFsStreamWrapper::dir_closedir
     */
    function testDir()
    {
        $e = array(
            '.',
            '..',
            'PHP',
            'TurkishUtf8Test.php',
            'Utf8',
            'Utf8Test.php',
            'µ€',
        );
        $d = array();

        $h = opendir(self::$dir . '/..');

        while (false !== $f = readdir($h)) $d[] = $f;

        sort($d);
        $this->assertSame($e, $d);
        rewinddir($h);

        $e = array();
        while (false !== $f = readdir($h)) $e[] = $f;

        closedir($h);

        sort($e);
        $this->assertSame($d, $e);
    }

    /**
     * @covers Patchwork\Utf8\WinFsStreamWrapper::rename
     * @covers Patchwork\Utf8\WinFsStreamWrapper::unlink
     */
    function testFileOp()
    {
        $f = self::$dir . '/déjà';
        $t = self::$dir . '/Δ';
        fclose(fopen($f, 'wb'));
        $this->assertTrue(rename($f, $t));
        $this->assertTrue(unlink($t));
    }

    /**
     * @covers Patchwork\Utf8\WinFsStreamWrapper::fopen
     * @covers Patchwork\Utf8\WinFsStreamWrapper::fwrite
     * @covers Patchwork\Utf8\WinFsStreamWrapper::fread
     * @covers Patchwork\Utf8\WinFsStreamWrapper::fclose
     */
    function testFilePutGetContents()
    {
        $f = self::$dir . '/déjà';
        $d = implode('', array_map('chr', range(0, 255)));

        $this->assertSame(strlen($d), file_put_contents($f, $d));
        $this->assertSame($d, file_get_contents($f));

        unlink($f);
    }

    /**
     * @covers Patchwork\Utf8\WinFsStreamWrapper::fopen
     * @covers Patchwork\Utf8\WinFsStreamWrapper::fclose
     */
    function testFopenX()
    {
        $f = self::$dir . '/déjà';

        $h = fopen($f, 'xb');
        $this->assertTrue(fclose($h));

        $this->assertFalse(@fopen($f, 'xb'));

        unlink($f);
    }

    /**
     * @covers Patchwork\Utf8\WinFsStreamWrapper::mkdir
     * @covers Patchwork\Utf8\WinFsStreamWrapper::rmdir
     */
    function testMkdir()
    {
        $this->assertTrue(file_exists(self::$dir));
        $this->assertFalse(mkdir(self::$dir));

        $this->assertTrue(mkdir(self::$dir . '/à/é/ï/', 0777, true));

        $this->assertFalse(@rmdir(self::$dir . '/à'));

        $this->assertTrue(rmdir(self::$dir . '/à/é/ï/'));
        $this->assertTrue(rmdir(self::$dir . '/à/é'));
        $this->assertTrue(rmdir(self::$dir . '/à'));
    }

    /**
     * @covers Patchwork\Utf8\WinFsStreamWrapper::url_stat
     */
    function testStat()
    {
        $this->assertTrue(is_dir(self::$dir));
    }

    /**
     * @requires PHP 5.4.0
     * @covers Patchwork\Utf8\WinFsStreamWrapper::stream_metadata
     * @covers Patchwork\Utf8\WinFsStreamWrapper::unlink
     */
    function testTouch()
    {
        $this->assertTrue(touch(self::$dir . '/héhé'));
        $this->assertTrue(file_exists(self::$dir . '/héhé'));
        $this->assertTrue(unlink(self::$dir . '/héhé'));
    }
}
