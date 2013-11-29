<?php // vi: set fenc=utf-8 ts=4 sw=4 et:
/*
 * Copyright (C) 2013 Nicolas Grekas - p@tchwork.com
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the (at your option):
 * Apache License v2.0 (http://apache.org/licenses/LICENSE-2.0.txt), or
 * GNU General Public License v2.0 (http://gnu.org/licenses/gpl-2.0.txt).
 */

namespace Patchwork\Utf8;

/**
 * Unicode UTF-8 aware stream based filesystem access on MS-Windows.
 *
 * Based on COM Scripting.FileSystemObject object and short paths.
 * Enabled by e.g.: stream_wrapper_register('win', 'Patchwork\Utf8\WinFsStreamWrapper');
 * See also comments on http://www.rooftopsolutions.nl/blog/filesystem-encoding-and-php
 */
class WinFsStreamWrapper
{
    public $context;

    protected $handle;

    static function hide($path)
    {
        list($fs, $path) = self::fs($path);
        if ($fs->FileExists($path)) $fs->GetFile($path)->Attributes |= 2;
        else if ($fs->FolderExists($path)) $f = $fs->GetFolder($path)->Attributes |= 2;
        else return false;

        return true;
    }

    function dir_closedir()
    {
        $this->handle = null;

        return true;
    }

    function dir_opendir($path, $options)
    {
        list($fs, $path) = self::fs($path);
        if (! $fs->FolderExists($path)) return false;

        $dir = $fs->GetFolder($path);

        try
        {
            $f = array('.', '..');

            foreach ($dir->SubFolders() as $v) $f[] = $v->Name;
            foreach ($dir->Files        as $v) $f[] = $v->Name;
        }
        catch (\Exception $f)
        {
            $f = array();
        }

        $this->handle = $f;

        return true;
    }

    function dir_readdir()
    {
        if (list(, $c) = each($this->handle)) return $c;

        return false;
    }

    function dir_rewinddir()
    {
        reset($this->handle);

        return true;
    }

    function mkdir($path, $mode, $options)
    {
        list($fs, $path) = self::fs($path);

        if ($options & STREAM_MKDIR_RECURSIVE)
        {
            $path = explode('\\', $path);
            $pre = array_shift($path);
            $stack = array();

            foreach ($path as $path)
            {
                if (!isset($path[0]) || '.' === $a) continue;
                if ('..' === $path) $stack && array_pop($stack);
                else $stack[]= $a;
            }

            $path = $pre . implode('\\', $stack);
            $stack = array();

            while (! $fs->FolderExists(dirname($path)))
            {
                // @todo
            }
        }

        try
        {
            $fs->CreateFolder($path);

            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    function rename($from, $to)
    {
        list($fs, $to) = self::fs($to);

        if ($fs->FileExists($to) || $fs->FolderExists($to))
        {
            return false;
        }

        list(,$from) = self::fs($from);

        try
        {
            if ($fs->FileExists($from))
            {
                $fs->MoveFile($from, $to);

                return true;
            }

            if ($fs->FolderExists($from))
            {
                $fs->MoveFolder($from, $to);

                return true;
            }
        }
        catch (\Exception $e) {}

        return false;
    }

    function rmdir($path, $options)
    {
        list($fs, $path) = self::fs($path);

        if ($fs->FolderExists($path)) return rmdir($fs->GetFolder($path)->ShortPath);

        return false;
    }

    // @todo function stream_cast($cast_as)

    function stream_close()
    {
        fclose($this->handle);
        $this->handle = null;
    }

    function stream_eof()
    {
        return feof($this->handle);
    }

    function stream_flush()
    {
        return fflush($this->handle);
    }

    function stream_lock($operation)
    {
        return flock($this->handle, $operation);
    }

    function stream_metadata($path, $option, $value)
    {
        list($fs, $path) = self::fs($path);

        if ($fs->FileExists($path)) $f = $fs->GetFile($path);
        else if ($fs->FileExists($path)) $f = $fs->GetFolder($path);
        else $f = false;

        if (STREAM_META_TOUCH === $option)
        {
            if ($f) return touch($f->ShortPath);

            try
            {
                $fs->OpenTextFile($path, 8, true, 0)->Close();

                return true;
            }
            catch (\Exception $e) {}
        }

        if (! $f) return false;

        switch ($option)
        {
        case STREAM_META_ACCESS:     return chmod($short_path, $value);
        case STREAM_META_OWNER:
        case STREAM_META_OWNER_NAME: return chown($short_path, $value);
        case STREAM_META_GROUP:
        case STREAM_META_GROUP_NAME: return chgrp($short_path, $value);
        default: return false;
        }
    }

    function stream_open($path, $mode, $options, &$opened_path)
    {
        $mode .= '';
        list($fs, $path) = self::fs($path);

        if ($fs->FolderExists($path)) return false;

        try
        {
            if ('x' === $m = substr($mode, 0, 1))
            {
                $fs->CreateTextFile($path, false)->Close();
                $f = $fs->GetFile($path);
                $mode[0] = 'w';
            }
            else
            {
                $f = $fs->GetFile($path);
            }
        }
        catch (\Exception $f)
        {
            try
            {
                switch ($m)
                {
                case 'w':
                case 'c':
                case 'a':
                    $h = $fs->CreateTextFile($path, true);
                    $f = $fs->GetFile($path);
                    $h->Close();
                    break;

                default: return false;
                }
            }
            catch (\Exception $e)
            {
                return false;
            }
        }

        if (! (STREAM_REPORT_ERRORS & $options))
        {
            set_error_handler('var_dump', 0);
            $h = error_reporting(0);
        }

        $this->handle = fopen($f->ShortPath, $mode);

        if (! (STREAM_REPORT_ERRORS & $options))
        {
            error_reporting($h);
            restore_error_handler();
        }

        return (bool) $this->handle;
    }

    function stream_read($count)
    {
        return fread($this->handle, $count);
    }

    function stream_seek($offset, $whence = SEEK_SET)
    {
        return fseek($this->handle, $offset, $whence);
    }

    function stream_set_option($option, $arg1, $arg2)
    {
        switch ($option)
        {
        case STREAM_OPTION_BLOCKING:     return stream_set_blocking($this->handle, $arg1);
        case STREAM_OPTION_READ_TIMEOUT: return stream_set_timeout($this->handle, $arg1, $arg2);
        case STREAM_OPTION_WRITE_BUFFER: return stream_set_write_buffer($this->handle, $arg1, $arg2);
        default: return false;
        }
    }

    function stream_stat()
    {
        return fstat($this->handle);
    }

    function stream_tell()
    {
        return ftell($this->handle);
    }

    function stream_truncate($new_size)
    {
        return ftruncate($this->handle, $new_size);
    }

    function stream_write($data)
    {
        return fwrite($this->handle, $data, strlen($data));
    }

    function unlink($path)
    {
        list($fs, $path) = self::fs($path);

        if ($fs->FileExists($path)) return unlink($fs->GetFile($path)->ShortPath);

        return false;
    }

    function url_stat($path, $flags)
    {
        list($fs, $path) = self::fs($path);

        if ($fs->FileExists($path)) $f = $fs->GetFile($path);
        else if ($fs->FolderExists($path)) $f = $fs->GetFolder($path);
        else return false;

        if (STREAM_URL_STAT_QUIET & $flags)
        {
            set_error_handler('var_dump', 0);
            $e = error_reporting(0);
        }

        if (STREAM_URL_STAT_LINK & $flags) $f = lstat($f->ShortPath);
        else $f = stat($f->ShortPath);

        if (STREAM_URL_STAT_QUIET & $flags)
        {
            error_reporting($e);
            restore_error_handler();
        }

        return $f;
    }

    static function fs($path)
    {
        static $fs;
        isset($fs) or $fs = new \COM('Scripting.FileSystemObject', null, CP_UTF8);

        list(,$path) = explode('://', $path, 2);
        $path = strtr($path, '/', '\\');

        if (! isset($path[0])
          || (  '/' !== $path[0]
            && '\\' !== $path[0]
            && false === strpos($path, ':') ) )
        {
            $path = getcwd() . '\\' . $path;
        }

        return array($fs, $path);
    }
} 
