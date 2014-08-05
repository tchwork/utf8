<?php

namespace Patchwork\Tests\Utf8;

if (! defined('STREAM_META_TOUCH'))
{
    define('STREAM_META_TOUCH',      1);
    define('STREAM_META_ACCESS',     2);
    define('STREAM_META_OWNER',      3);
    define('STREAM_META_OWNER_NAME', 4);
    define('STREAM_META_GROUP',      5);
    define('STREAM_META_GROUP_NAME', 6);
}

/**
 * @covers Patchwork\Utf8\WindowsStreamWrapper::<!public>
 */
class WindowsStreamWrapperTest extends \PHPUnit_Framework_TestCase
{
    protected static $dir;

    static function setUpBeforeClass()
    {
        if (extension_loaded('com_dotnet'))
        {
            stream_wrapper_register('win', 'Patchwork\Utf8\WindowsStreamWrapper');
            $dir = __DIR__;
            list(,$dir) = \Patchwork\Utf8\WindowsStreamWrapper::fs($dir, false); // Convert $dir to UTF-8
            self::$dir = 'win://' . $dir . '/../µ€';
            mkdir(self::$dir);
        }
    }

    static function  tearDownAfterClass()
    {
        if (extension_loaded('com_dotnet'))
        {
            list($fs, $path) = \Patchwork\Utf8\WindowsStreamWrapper::fs(self::$dir);
            if ($fs->FolderExists($path)) $fs->GetFolder($path)->Delete(true);
            stream_wrapper_unregister('win');
        }
    }

    function setUp()
    {
        if (! extension_loaded('com_dotnet')) $this->markTestSkipped('Extension com_dotnet is required.');
    }

    /**
     * @covers Patchwork\Utf8\WindowsStreamWrapper::fs
     */
    function testRelDir()
    {
        $this->assertTrue(file_exists(self::$dir));
        $cwd = getcwd();
        chdir(__DIR__ . '/..');
        $this->assertTrue(file_exists('win://./µ€'));
        chdir($cwd);
    }

    /**
     * @covers Patchwork\Utf8\WindowsStreamWrapper::dir_opendir
     * @covers Patchwork\Utf8\WindowsStreamWrapper::dir_readdir
     * @covers Patchwork\Utf8\WindowsStreamWrapper::dir_rewinddir
     * @covers Patchwork\Utf8\WindowsStreamWrapper::dir_closedir
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
     * @covers Patchwork\Utf8\WindowsStreamWrapper::rename
     * @covers Patchwork\Utf8\WindowsStreamWrapper::unlink
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
     * @covers Patchwork\Utf8\WindowsStreamWrapper::stream_open
     * @covers Patchwork\Utf8\WindowsStreamWrapper::stream_write
     * @covers Patchwork\Utf8\WindowsStreamWrapper::stream_read
     * @covers Patchwork\Utf8\WindowsStreamWrapper::stream_eof
     * @covers Patchwork\Utf8\WindowsStreamWrapper::stream_close
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
     * @covers Patchwork\Utf8\WindowsStreamWrapper::fopen
     * @covers Patchwork\Utf8\WindowsStreamWrapper::fclose
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
     * @covers Patchwork\Utf8\WindowsStreamWrapper::mkdir
     * @covers Patchwork\Utf8\WindowsStreamWrapper::rmdir
     */
    function testMkdir()
    {
        $this->assertTrue(file_exists(self::$dir));
        $this->assertFalse(@mkdir(self::$dir));

        $d = array(
            'fr' => 'déjà',
            'jp' => 'は、広く使われているオープンソースの汎用スクリプト言語です。',
            'cn' => '是一种被广泛应用的开放源代码的多用途脚本语言',
            'ru' => 'это распространенный язык программирования',
        );

        foreach ($d as $d)
        {
            $this->assertTrue(mkdir(self::$dir . '/' . $d));

            // @todo: remove the @ and fixme
            @fclose(fopen(self::$dir . '/' . $d . '/' . $d, 'wb'));
            @unlink(self::$dir . '/' . $d . '/' . $d);

            $this->assertTrue(rmdir(self::$dir . '/' . $d));
        }

        $h = @fopen(self::$dir . '/' . $d, 'wb');
        $this->assertFalse($h);
        $this->markTestIncomplete('fopen() should not fail, this has to be fixed.');
    }

    /**
     * @covers Patchwork\Utf8\WindowsStreamWrapper::mkdir
     * @covers Patchwork\Utf8\WindowsStreamWrapper::rmdir
     */
    function testMkdirRecursive()
    {
        $this->assertTrue(mkdir(self::$dir . '/à/种/э/', 0777, true));

        $this->assertFalse(@rmdir(self::$dir . '/à'));

        $this->assertTrue(rmdir(self::$dir . '/à/种/э/'));
        $this->assertTrue(rmdir(self::$dir . '/à/种'));
        $this->assertTrue(rmdir(self::$dir . '/à'));
    }

    /**
     * @covers Patchwork\Utf8\WindowsStreamWrapper::url_stat
     */
    function testStat()
    {
        $this->assertTrue(is_dir(self::$dir));
    }

    /**
     * @covers Patchwork\Utf8\WindowsStreamWrapper::stream_metadata
     * @covers Patchwork\Utf8\WindowsStreamWrapper::unlink
     */
    function testStreamtMetadata()
    {
        $win = new \Patchwork\Utf8\WindowsStreamWrapper;
        $f = self::$dir . '/это';

        $this->assertFalse(file_exists($f));
        $this->assertTrue($win->stream_metadata($f, STREAM_META_TOUCH, time()));
        $this->assertTrue(file_exists($f));
        $this->assertTrue($win->stream_metadata($f, STREAM_META_TOUCH, time()));
        $this->assertTrue($win->stream_metadata($f, STREAM_META_ACCESS, 0777));
        $this->assertFalse($win->stream_metadata($f, STREAM_META_OWNER, 0));
        $this->assertFalse($win->stream_metadata($f, STREAM_META_GROUP, 0));

        $this->assertTrue(unlink($f));
    }
}
