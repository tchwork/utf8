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
 * Unicode aware stream based filesystem access on MS-Windows.
 *
 * Based on COM Scripting.FileSystemObject object.
 * See also comments on http://www.rooftopsolutions.nl/blog/filesystem-encoding-and-php
 */
class WinFsStreamWrapper
{
    public $context;

    protected static $fs;

    protected $path;
    protected $state;

    static function hide($path)
    {
        new self;
        $path = self::abs($path);
        if (self::$fs->FileExists($path)) self::$fs->GetFile($path)->Attributes |= 2;
        else if (self::$fs->FolderExists($path)) $f = self::$fs->GetFolder($path)->Attributes |= 2;
        else return false;

        return true;
    }

    function __construct()
    {
        isset(self::$fs) or self::$fs = new \COM('Scripting.FileSystemObject', null, CP_UTF8);
    }

    function dir_closedir()
    {
        $this->state = null;
        $this->path = null;

        return true;
    }

    function dir_opendir($path, $options)
    {
        $path = self::abs($path);
        if (! self::$fs->FolderExists($path)) return false;

        $this->path = $path;
        $dir = self::$fs->GetFolder($path);

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

        $this->state = $f;

        return true;
    }

    function dir_readdir()
    {
        if (list(, $c) = each($this->state)) return $c;

        return false;
    }

    function dir_rewinddir()
    {
        reset($this->state);

        return true;
    }

    function mkdir($path, $mode, $options)
    {
        $path = self::abs($path);

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

            while (! self::getFs()->FolderExists(dirname($path)))
            {
                //TODO
            }
        }

        try
        {
            self::$fs->CreateFolder($path);

            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    function rename($from, $to)
    {
        $to = self::abs($to);

        if (self::$fs->FileExists($to) || self::$fs->FolderExists($to))
        {
            return false;
        }

        $from = self::abs($from);

        try
        {
            if (self::$fs->FileExists($from))
            {
                self::$fs->MoveFile($from, $to);

                return true;
            }

            if (self::$fs->FolderExists($from))
            {
                self::$fs->MoveFolder($from, $to);

                return true;
            }
        }
        catch (\Exception $e) {}

        return false;
    }

    // @todo? honor $options = STREAM_MKDIR_RECURSIVE
    function rmdir($path, $options)
    {
        $path = self::abs($path);

        try
        {
            if (self::$fs->FolderExists($path))
            {
                $dir = self::$fs->GetFolder($path);

                foreach ($dir->SubFolders() as $v) return false;$f[] = $v->Name;
                foreach ($dir->Files        as $v) return false;

                $dir->Delete(true);

                return true;
            }
        }
        catch (\Exception $e) {}

        return false;
    }

    // @todo function stream_cast($cast_as)

    function stream_close()
    {
        $this->state->Close();
        $this->state = null;
        $this->path = null;
    }

    function stream_eof()
    {
        return $this->state->AtEndOfStream;
    }

    function stream_flush()
    {
        return true;
    }

    // @todo stream_lock($operation)

    function stream_metadata($path, $option, $value)
    {
        switch ($option)
        {
        case STREAM_META_ACCESS:     // chmod()
        case STREAM_META_TOUCH:      // touch()
        case STREAM_META_OWNER:      // chown()
        case STREAM_META_OWNER_NAME: // chown()
        case STREAM_META_GROUP:      // chgrp()
        case STREAM_META_GROUP_NAME: // chgrp()
        }

        return false;
    }

    // @todo: honor $options = STREAM_USE_PATH | STREAM_REPORT_ERRORS
    function stream_open($path, $mode, $options, &$opened_path)
    {
        $path = self::abs($path);

        if ($options & STREAM_USE_PATH) $opened_path = $path;

        if ('b' !== substr($mode, -1)) return false;
        else $mode = substr($mode, 0, -1);

        try
        {
            switch ($mode)
            {
            case 'r':  // ro at start
                $this->state = self::$fs->OpenTextFile($path, 1, false, 0);
                break;

            case 'w':  // wo at start, create or trunc
                $this->state = self::$fs->OpenTextFile($path, 2, true, 0);
                break;

            case 'a':  // wo at end, create
                $this->state = self::$fs->OpenTextFile($path, 8, true, 0);
                break;

            case 'x':  // wo at start, create or fail
            case 'x+': // rw at start, create or fail
                $this->state = self::$fs->CreateTextFile($path, false);
                break;

            case 'w+': // rw at start, create or trunc
                $this->state = self::$fs->CreateTextFile($path, true);
                break;

            case 'c':  // wo at start, create
            case 'r+': // rw at start
            case 'a+': // rw at end, create
            case 'c+': // rw at start, create
            default: return false;
            }

            $this->path = $path;

            return true;
        }
        catch (\Exception $e)
        {
        }

        return false;
    }

    function stream_read($count)
    {
        return $this->state->Read($count);
    }

    function stream_seek($offset, $whence = SEEK_SET)
    {
        switch ($whence)
        {
        case SEEK_CUR:
            $this->state->Skip($offset);

            return true;

        case SEEK_SET:
        case SEEK_END:
        }

        return false;
    }

    // @todo: stream_set_option($option, $arg1, $arg2)

    function stream_stat()
    {
        return $this->url_stat($this->path);
    }

    // @todo: stream_tell()

    // @todo: stream_truncate($new_size)

    function stream_write($data)
    {
        $this->state->Write($data);

        return strlen($data);
    }

    function unlink($path)
    {
        $path = self::abs($path);

        try
        {
            if (self::$fs->FileExists($path))
            {
                self::$fs->GetFile($path)->Delete(true);

                return true;
            }
        }
        catch (\Exception $e) {}

        return false;
    }

    // @todo: honor $flags = STREAM_URL_STAT_QUIET | STREAM_URL_STAT_LINK
    function url_stat($path, $flags)
    {
        $path = self::abs($path);

        if (self::$fs->FileExists($path)) $f = self::$fs->GetFile($path);
        else if (self::$fs->FolderExists($path)) $f = self::$fs->GetFolder($path);
        else return false;

        $s = array(
            'dev' => 'device number',
            'ino' => 0,
            'mode' => 'inode protection mode',
            'nlink' => 'number of links',
            'uid' => 0,
            'gid' => 0,
            'rdev' => 'device type, if inode device',
            'size' => $f->Size, // Folders ?
            'atime' => variant_date_to_timestamp($f->DateLastAccessed),
            'mtime' => variant_date_to_timestamp($f->DateLastModified),
            'ctime' => variant_date_to_timestamp($f->DateCreated),
            'blksize' => -1,
            'blocks' => -1,
        );

        return $s + array_values($s);
    }

    static function abs($path)
    {
        $path = strtr($path, '/', '\\');

        if (isset($path[0]))
        {
            if ('/' === $path[0] || '\\' === $path[0]) return $path;
            if (false !== strpos($path, ':')) return $path;
        }

        return getcwd() . '\\' . $path;
    }
} 
