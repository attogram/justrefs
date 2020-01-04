<?php
/**
 * Just Refs
 * Filesystem Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

class Filesystem extends Base
{
    /**
     * @param string $name
     * @return bool
     */
    public function exists($name) 
    {
        $path = $this->getPath($name);
        if (empty($path)) {
            //$this->verbose('exists: false: no path');
            return false;
        }
        if (!file_exists($path)) {
            //$this->verbose('exists: false: file not found');
            return false;
        }
        if (!is_readable($path)) {
            //$this->verbose('exists: false: file not readable');
            return false;
        }
        //$this->verbose("exists: OK: name: $name path: $path");
        return true;
    }

    /**
     * @param string $name
     * @return string - file contents, or empty string on error
     */
    public function get($name) 
    {
        if (!$this->exists($name)) {
            //$this->verbose('get: ERROR: does not exist');
            return '';
        }
        $path = $this->getPath($name);
        if (empty($path)) {
            //$this->verbose('get: ERROR: no path');
            return '';
        }
        $contents = @file_get_contents($path);
        if (empty($contents)) {
            $this->verbose('get: ERROR: no contents');
            return '';
        }
        $this->verbose("get: OK: name: $name strlen.contents: " . strlen($contents));
        return $contents;
    }

    /**
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function set($name, $value) 
    {
        $path = $this->getPath($name);
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        array_pop($parts);
        $dir = '';
        foreach ($parts as $part) {
            $dir .= $part . DIRECTORY_SEPARATOR;
            if (!is_dir($dir)) {
                $this->verbose('set: mkdir: ' . $dir);
                mkdir($dir);
            }
        }
        $bytes = file_put_contents($path, $value);
        if (false === $bytes) {
            $this->verbose('set: FAILED write path: ' . $path);
            return false;
        }
        $this->verbose("set: OK: name: $name bytes: $bytes  path: $path");
        return true;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function delete($name)
    {
        $this->verbose('delete: name: ' . $name);
        if (!$this->exists($name)) {
            $this->verbose('delete: ERROR: does not exist');
            return false;
        }
        $file = $this->getPath($name);
        if (!$file) {
            $this->verbose('delete: ERROR: path empty');
            return false;
        }
        if (unlink($file)) {
            return true;
        }
        $this->verbose('delete: ERROR: unlink failed');
        return false;
    }

    /**
     * @param string $name
     * @return int|false - unix time stamp, or false
     */
    public function age($name)
    {
        $this->verbose('age: name: ' . $name);
        if (!$this->exists($name)) {
            $this->verbose('age: ERROR: does not exist');
            return false;
        }
        $file = $this->getPath($name);
        if (!$file) {
            $this->verbose('age: ERROR: path empty');
            return false;
        }
        return filemtime($file);
    }

    /**
     * @param string $name
     * @return string
     */
    private function getPath($name)
    {
        $md5 = md5($name);
        if (empty($md5)) {
            $this->verbose('getPath: ERROR: md5 failed');
            return '';
        }
        $first = substr($md5, 0, 1);
        if (!strlen($first)) {
            $this->verbose('getPath: ERROR: extract first failed');
            return '';
        }
        $second = substr($md5, 1, 2);
        if (!strlen($second) == 2) {
            $this->verbose('getPath: ERROR: extract second failed');
            return '';
        }
        $path = $this->basePath . $first . DIRECTORY_SEPARATOR . $second . DIRECTORY_SEPARATOR . $md5 . '.gz';
        return $path;
    }
}
