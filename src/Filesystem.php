<?php
/**
 * Raw Filesystem
 */
declare(strict_types = 1);

namespace Raw;

class Filesystem
{
    const VERSION = '0.0.1';

    /** @var bool $verbose */
    public $verbose = false;

    /** @var string $base - base path to cache directory */
    public $base = '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;

    /**
     * @param string $name
     * @return bool
     */
    public function exists($name) 
    {
        //$this->verbose('exists: name: ' . $name);
        $path = $this->getPath($name);
        if (empty($path)) {
            $this->verbose('exists: false: no path');
            return false;
        }
        if (!file_exists($path)) {
            $this->verbose('exists: false: file not found');
            return false;
        }

        if (!is_readable($path)) {
            $this->verbose('exists: false: file not readable');
            return false;
        }

        $this->verbose("exists: OK: name: $name path: $path");

        return true;
    }

    /**
     * @param string $name
     * @return string
     */
    public function get($name) 
    {
        //$this->verbose('get: name: ' . $name);

        if (!$this->exists($name)) {
            $this->verbose('get: ERROR: does not exist');
            return '';
        }

        $path = $this->getPath($name);
        if (empty($path)) {
            $this->verbose('get: ERROR: no path');
            return '';
        }
        //$this->verbose('get: path: ' . $path);

        $contents = file_get_contents($path);

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
        //$this->verbose('set: name: ' . $name . ' strlen.value: ' . strlen($value));

        $path = $this->getPath($name);
        //$this->verbose('set: path: ' . $path);

        $parts = explode(DIRECTORY_SEPARATOR, $path);
        array_pop($parts);
        //$this->verbose('set: parts: ' . print_r($parts, true));
        $dir = '';
        foreach ($parts as $part) {
            $dir .= $part . DIRECTORY_SEPARATOR;
            //$this->verbose('set: dir: ' . $dir);
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
        return false;
    }
    
    /**
     * @param mixed $message (otional, default empty string)
     * @return void
     */
    public function verbose($message = '')
    {
        if ($this->verbose) {
            print gmdate('Y-m-d H:i:s') . ': ' . htmlentities(print_r($message, true)) . "\n";
        }
    }

    /**
     * @param string $name
     * @return string
     */
    private function getPath($name)
    {
        //$this->verbose('getPath: name: ' . $name);
        $md5 = md5($name);
        if (empty($md5)) {
            $this->verbose('getPath: ERROR: md5 failed');
            return '';
        }
        $first = substr($md5, 0, 2);
        if (empty($first)) {
            $this->verbose('getPath: ERROR: extract first failed');
            return '';
        }
        $second = substr($md5, 2, 2);
        if (empty($second)) {
            $this->verbose('getPath: ERROR: extract second failed');
            return '';
        }

        $path = $this->base . $first 
            . DIRECTORY_SEPARATOR . $second 
            . DIRECTORY_SEPARATOR . $md5 . '.gz';

        //$this->verbose('getPath: path: ' . $path);

        return $path;
    }
}
