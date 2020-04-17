<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * FilesystemCache Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use Illuminate\Cache\FileStore;
use Illuminate\Cache\Repository;
use Illuminate\Filesystem\Filesystem;

class FilesystemCache
{
    const cacheDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache';

    /**
     * Initialize Cache object
     *
     * @return \Illuminate\Cache\Repository
     */
    public function init()
    {
        return new Repository(
            new FileStore(
                new Filesystem(),
                self::cacheDirectory
            )
        );
    }

    /**
     * Get Cache Age
     *
     * @param string $key - cache item key
     * @return string - date string
     */
    public function getAge($key)
    {
        // build filename in Illuminate\Cache style
        $parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);
        $filename = self::cacheDirectory
            . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts)
            . DIRECTORY_SEPARATOR . $hash;

        // If file does not exist...
        if (!is_readable($filename)) {
            return gmdate("NOT FOUND: Y-m-d H:i:s", now());
        }

        // get inode change time of file
        $lastModified = filectime($filename);

        // if filemtime fails...
        if (!$lastModified) {
            return gmdate("ERROR: Y-m-d H:i:s", now());
        }

        return gmdate("Y-m-d H:i:s", $lastModified);
    }
}
