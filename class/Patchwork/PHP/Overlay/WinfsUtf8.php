<?php /***** vi: set encoding=utf-8 expandtab shiftwidth=4: ****************
 *
 *   Copyright : (C) 2011 Nicolas Grekas. All rights reserved.
 *   Email     : p@tchwork.org
 *   License   : http://www.gnu.org/licenses/agpl.txt GNU/AGPL
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU Affero General Public License as
 *   published by the Free Software Foundation, either version 3 of the
 *   License, or (at your option) any later version.
 *
 ***************************************************************************/


// Unicode aware filesystem access on MS-Windows
// based on COM Scripting.FileSystemObject object and 8.3 ShortPaths
// See also comments on http://www.rooftopsolutions.nl/blog/filesystem-encoding-and-php

class Patchwork_PHP_Overlay_WinfsUtf8
{
    protected static $FS, $DIR;

    static function __constructStatic()
    {
        self::$FS = self::newCOM('Scripting.FileSystemObject');
    }

    static function newCOM($module, $server = null, $cp = CP_UTF8, $typelib = INF)
    {
        return INF === $typelib
            ? new COM($module, $server, $cp)
            : new COM($module, $server, $cp, $typelib);
    }

    static function hide($file)
    {
        self::$FS->GetFile($file)->Attributes |= 2; // Set hidden attribute
    }

    static function absPath($f)
    {
        $f = strtr($f, '/', '\\');

        if (isset($f[0]))
        {
            if ('/' === $f[0] || '\\' === $f[0]) return $f;
            if (false !== strpos($f, ':')) return $f;
        }

        return getcwd() . '\\' . $f;
    }

    static function &ls($dir)
    {
        try
        {
            $f = array('.', '..');

            $dir = self::$FS->getFolder(self::absPath($dir));

            foreach ($dir->SubFolders() as $v) $f[] = $v->Name;
            foreach ($dir->Files        as $v) $f[] = $v->Name;
        }
        catch (Exception $f)
        {
            $f = array();
        }

        unset($dir);

        return $f;
    }

    static function ShortPath($f)
    {
        $FS = self::$FS;

        $a = self::absPath($f);

        try
        {
            if ($FS->FileExists($a)  ) return $FS->GetFile  ($a)->ShortPath;
            if ($FS->FolderExists($a)) return $FS->GetFolder($a)->ShortPath;
        }
        catch (com_exception $e) {}

        return $f;
    }


    static function chgrp($f, $group) {return chgrp(self::ShortPath($f), $group);}
    static function chmod($f, $mode)  {return chmod(self::ShortPath($f), $mode);}
    static function chown($f, $user)  {return chown(self::ShortPath($f), $user);}

    static function copy($from, $to, $context = null)
    {
        if ($context || !self::$FS->FileExists(self::absPath($from)))
        {
            return copy($from, $to, $context);
        }

        try
        {
            self::$FS->CopyFile(self::absPath($from), self::absPath($to), true);
            return true;
        }
        catch (com_exception $e)
        {
            return false;
        }
    }

    static function file_exists($f)
    {
        $f = self::absPath($f);
        return self::$FS->FileExists($f) || self::$FS->FolderExists($f);
    }

    static function file_get_contents($f, $use_include_path = false, $context = null, $offset = 0, $maxlen = null)
    {
        return null === $maxlen
            ? file_get_contents(self::ShortPath($f), $use_include_path, $context, $offset)
            : file_get_contents(self::ShortPath($f), $use_include_path, $context, $offset, $maxlen);
    }

    static function file_put_contents($f, $data, $use_include_path = false, $context = null)
    {
        try {self::$FS->CreateTextFile(self::absPath($f), false)->Close();}
        catch (com_exception $e) {}

        return null === $context
            ? file_put_contents(self::ShortPath($f), $data, $use_include_path)
            : file_put_contents(self::ShortPath($f), $data, $use_include_path, $context);
    }

    static function file($f, $use_include_path = false, $context = null)
    {
        return null === $context
            ? file(self::ShortPath($f), $use_include_path)
            : file(self::ShortPath($f), $use_include_path, $context);
    }

    static function fileatime($f) {return fileatime(self::ShortPath($f));}
    static function filectime($f) {return filectime(self::ShortPath($f));}
    static function filegroup($f) {return filegroup(self::ShortPath($f));}
    static function fileinode($f) {return fileinode(self::ShortPath($f));}
    static function filemtime($f) {return filemtime(self::ShortPath($f));}
    static function fileowner($f) {return fileowner(self::ShortPath($f));}
    static function fileperms($f) {return fileperms(self::ShortPath($f));}
    static function filesize($f)  {return filesize (self::ShortPath($f));}
    static function filetype($f)  {return filetype (self::ShortPath($f));}

    static function fopen($f, $mode, $use_include_path = false, $context = null)
    {
        switch ($m = substr($mode, 0, 1))
        {
        case 'x': $mode[0] = 'w';
        case 'w':
        case 'a':
            try {self::$FS->CreateTextFile(self::absPath($f), false)->Close();}
            catch (com_exception $e)
            {
                if ('x' === $m) return false;
            }
        }

        return null === $context
            ? fopen(self::ShortPath($f), $mode, $use_include_path)
            : fopen(self::ShortPath($f), $mode, $use_include_path, $context);
    }

//  static function glob($f) {return glob($f);}

    static function is_dir($f)           {return is_dir       (self::ShortPath($f));}
    static function is_executable($f)    {return is_executable(self::ShortPath($f));}
    static function is_file($f)          {return is_file      (self::ShortPath($f));}
    static function is_readable($f)      {return is_readable  (self::ShortPath($f));}
    static function is_writable($f)      {return is_writable  (self::ShortPath($f));}
    static function is_writeable($f)     {return is_writeable (self::ShortPath($f));}

    static function mkdir($dir, $mode = 0777, $recursive = false, $context = null)
    {
        return mkdir($dir, $mode, $recursive);

        if (null !== $context) return mkdir($dir, $mode, $recursive, $context);

        $a = self::absPath($dir);

        if ($recursive && 0)
        {
            $a = explode('\\', $a);

            $pre = $a[0];
            array_shift($a);

            $b = array();

            foreach ($a as $a)
            {
                if (!isset($a[0]) || '.' === $a) continue;
                if ('..' === $a) $b && array_pop($b);
                else $b[]= $a;
            }

            $a = $pre . implode('\\', $b);

            $b = array();

            while (!self::$FS->FolderExists(dirname($a)))
            {
                //XXX TODO
            }
        }

        try
        {
            self::$FS->CreateFolder($a);
            return true;
        }
        catch (com_exception $e) {}

        return mkdir($dir, $mode, $recursive);
    }

    static function parse_ini_file($f, $process_sections = false) {return parse_ini_file(self::ShortPath($f), $process_sections);}

    static function readfile($f, $use_include_path = false, $context = null)
    {
        return null === $context
            ? readfile(self::ShortPath($f), $use_include_path)
            : readfile(self::ShortPath($f), $use_include_path, $context);
    }

    static function realpath($f) {return self::file_exists($f) ? self::$FS->GetAbsolutePathName(self::absPath($f)) : false;}

    static function rename($from, $to, $context = null)
    {
        if ($context) return rename($from, $to, $context);

        $FS = self::$FS;
        $from = self::absPath($from);
        $to   = self::absPath($to);

        if ($FS->FileExists($to))
        {
            try
            {
                $FS->DeleteFile($to, true);
            }
            catch (com_exception $e) {}
        }
        else if ($FS->FolderExists($to))
        {
            return false;
        }

        try
        {
            if ($FS->FileExists($from))
            {
                $FS->MoveFile($from, $to);
                return true;
            }

            if ($FS->FolderExists($from))
            {
                $FS->MoveFolder($from, $to);
                return true;
            }
        }
        catch (com_exception $e) {}

        return false;
    }

    static function rmdir($f, $context = null)
    {
        return null === $context
            ? rmdir(self::ShortPath($f))
            : rmdir(self::ShortPath($f), $context);
    }

    static function stat($f) {return stat(self::ShortPath($f));}

    static function touch($f, $time = null, $atime = null)
    {
        try {self::$FS->CreateTextFile(self::absPath($f), false)->Close();}
        catch (com_exception $e) {}

        return touch(self::ShortPath($f), $time, $atime);
    }

    static function unlink($f, $context = null)
    {
        return null === $context
            ? unlink(self::ShortPath($f))
            : unlink(self::ShortPath($f), $context);
    }

    static function dir($f)
    {
        return self::$FS->FolderExists(self::absPath($f)) ? new Patchwork_PHP_Overlay_WinfsUtf8_Directory($f) : dir($f);
    }

    static function closedir($d = null)
    {
        null === $d && $d = self::$DIR;
        return $d instanceof Patchwork_PHP_Overlay_WinfsUtf8_Directory ? $d->close() : closedir($d);
    }

    static function opendir($f, $context = null)
    {
        return self::$DIR = !$context && self::$FS->FolderExists(self::absPath($f)) ? new Patchwork_PHP_Overlay_WinfsUtf8_Directory($f) : opendir($f, $context);
    }

    static function readdir($d = null)
    {
        null === $d && $d = self::$DIR;
        return $d instanceof Patchwork_PHP_Overlay_WinfsUtf8_Directory ? $d->read() : readdir($d);
    }

    static function rewinddir($d = null)
    {
        null === $d && $d = self::$DIR;
        return $d instanceof Patchwork_PHP_Overlay_WinfsUtf8_Directory ? $d->rewind() : rewinddir($d);
    }

    static function scandir($f, $sorting_order = 0, $context = null)
    {
        if (null !== $context) return scandir($f, $sorting_order, $context);

        $c = self::ls($f);

        if (!$c) return scandir($f);

        sort($c);

        return $c;
    }

/*
    static function popen($f) {return popen(self::ShortPath($f));}
    static function exec($f) {return exec(self::ShortPath($f));}
    static function passthru($f) {return passthru(self::ShortPath($f));}
    static function proc_open($f) {return proc_open(self::ShortPath($f));}
    static function shell_exec($f) {return shell_exec(self::ShortPath($f));}
    static function ` `($f) {return ` `(self::ShortPath($f));}
    static function system($f) {return system(self::ShortPath($f));}
 */
}

class Patchwork_PHP_Overlay_WinfsUtf8_Directory extends Directory
{
    public $path, $handle;

    protected $childs = array();

    function __construct($path)
    {
        $this->path = $path;
        $this->handle = $this;
        $this->childs =& WIN::ls($path);

        if (!$this->childs)
        {
            $this->childs = scandir($path);
            $this->childs || $this->childs = array();
        }
    }

    function read()
    {
        return (list(, $c) = each($this->childs)) ? $c : false;
    }

    function rewind()
    {
        reset($this->childs);
    }

    function close()
    {
        unset($this->path, $this->handle, $this->childs);
    }
}
