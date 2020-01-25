<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Base Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function error_get_last;
use function get_class;
use function gmdate;
use function htmlentities;
use function microtime;
use function print_r;
use function round;

class Base
{
    const VERSION = '0.4.0';

    public $verbose; // @param bool $verbose - print verbose debug messages to STDOUT
    public $router; // Attogram\Router\Router
    public $template;   // Attogram\Justrefs\Template

    protected $siteName = 'Just Refs'; // @param string $siteName - The Name of the site!
    protected $basePath = // @param string $basePath - path to cache directory
        '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    protected $timer = []; // @param array $timer - array of start times
    protected $filesystem; // Attogram\Justrefs\Filesystem
    protected $mediaWiki;  // Attogram\Justrefs\MediaWiki

    /**
     * @param string $message (optional)
     */
    protected function verbose($message = '')
    {
        if ($this->verbose) {
            print '<pre>' . gmdate('Y-m-d H:i:s') . ': ' . get_class($this) 
                . ': ' . htmlentities(print_r($message, true)) . '</pre>';
        }
    }

    /**
     * @param string $query
     * @return string
     */
    public function getLink($query)
    {
        return 'r/' . $this->encodeLink($query);
    }

    /**
     * @param string $query
     * @return string
     */
    protected function encodeLink($query)
    {
        // @see https://www.mediawiki.org/wiki/Manual:PAGENAMEE_encoding
        $replacers = [
            ' ' => '_',
            '%' => '%25', // do first before any other %## replacers
            '"' => '%22',
            '&' => '%26',
            "'" => '%27',
            '+' => '%2B',
            '=' => '%3D',
            '?' => '%3F',
            "\\" => '%5C',
            '^' => '%5E',
            '`' => '%60',
            '~' => '%7E',
        ];
        foreach ($replacers as $old => $new) {
            $query = str_replace($old, $new, $query);
        }
        return $query;
    }

    protected function initFilesystem()
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->verbose = $this->verbose;
    }

    protected function initMediaWiki()
    {
        $this->mediaWiki = new MediaWiki();
        $this->mediaWiki->verbose = $this->verbose;
    }

    /**
     * @param string $name
     */
    protected function startTimer($name)
    {
        $this->timer[$name] = microtime(true);
    }

    /**
     * @param string $name
     * @return float
     */
    protected function endTimer($name)
    {
        if (empty($this->timer[$name])) {
            return 0.0;
        }

        return round(microtime(true) - $this->timer[$name], 4);
    }

    public function shutdownHandler()
    {
        $error = error_get_last();
        if (!$error) {
            exit;
        }
        $this->verbose = true;
        $this->verbose('FATAL ERROR: ' . print_r($error, true));
        exit;
    }
}
