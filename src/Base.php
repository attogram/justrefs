<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Base Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

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

    protected $topic;
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
            '\\' => '%5C',
            '^' => '%5E',
            '`' => '%60',
            '~' => '%7E',
        ];
        foreach ($replacers as $old => $new) {
            $query = str_replace($old, $new, $query);
        }
        return $query;
    }

    /**
     * set $this->topic to string from URL elements, or empty string
     */
    protected function setTopicFromUrl()
    {
        $this->topic = $this->router->getVar(0);
        if ($this->router->getVar(1)) {
            $this->topic .= '/' . $this->router->getVar(1);
            if ($this->router->getVar(2)) {
                $this->topic .= '/' . $this->router->getVar(2);
                if ($this->router->getVar(3)) {
                    $this->topic .= '/' . $this->router->getVar(3);
                    if ($this->router->getVar(4)) {
                        $this->topic .= '/' . $this->router->getVar(4);
                    }
                }
            }
        }
        if (!is_string($this->topic) || !strlen($this->topic)) {
            $this->topic = '';
            return;
        }
        // format query
        $this->topic = trim($this->topic);
        $this->topic = str_replace('_', ' ', $this->topic);
        $this->topic = urldecode($this->topic);
        if (!is_string($this->topic) || !strlen($this->topic)) {
            $this->topic = '';
        }
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

    /**
     * @param string $message
     * @param string $reresh - refresh query link
     * @return void
     */
    public function error404($message = 'Page Not Found', $refresh = '')
    {
        header('HTTP/1.0 404 Not Found');
        $this->template->include('html_head');
        $this->template->include('header');
        print '<div class="body"><h1>Error 404</h1><h2>' . $message . '</h2>';
        if ($refresh) {
            print '<p><small><a href="'
                . $this->template->get('home') . 'refresh/'
                . $this->encodeLink($refresh)
                . '">Attempt Refresh</a></small></p>';
        }
        print '</div>';
        $this->template->include('footer');
        exit;
    }
}
