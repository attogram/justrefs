<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Base Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function get_class;
use function htmlentities;
use function microtime;
use function print_r;
use function round;

class Base
{
    const VERSION = '0.4.9';

    /**
     * @var bool - print verbose debug messages to STDOUT
     */
    public $verbose = false;

    /**
     * @var \Attogram\Router\Router
     */
    public $router;

    /**
     * @var \Attogram\Justrefs\Template
     */
    public $template;

    /**
     * @var \Attogram\Justrefs\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Attogram\Justrefs\Mediawiki
     */
    protected $mediawiki;

    /**
     * @var string - The Name of the site!
     */
    protected $siteName = 'Just Refs';

    /**
     * @var string - extraction source url
     */
    protected $source = 'https://en.wikipedia.org/wiki/';

    /**
     * @var string - path to cache directory
     */
    protected $basePath = '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;

    /**
     * @var array - array of start times
     */
    protected $timer = [];

    /**
     * @var string - page topic
     */
    protected $topic;

    /**
     * @param bool $verbose (optional, default false)
     */
    public function __construct($verbose = false)
    {
        if ($verbose) {
            $this->verbose = true;
        }
    }

    /**
     * @param string $message (optional)
     */
    protected function verbose($message = '')
    {
        if ($this->verbose) {
            print '<pre>' . (new \DateTime())->format('u') . ': ' . get_class($this)
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
     * @see https://www.mediawiki.org/wiki/Manual:PAGENAMEE_encoding
     */
    protected function encodeLink($query)
    {
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
        $this->filesystem = new Filesystem($this->verbose);
    }

    protected function initMediawiki()
    {
        $this->mediawiki = new Mediawiki($this->verbose);
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
     * @param string $refresh - refresh query link
     * @return void
     */
    public function error404($message = 'Page Not Found', $refresh = '')
    {
        header('HTTP/1.0 404 Not Found');
        $this->template->include('html_head');
        $this->template->include('header');
        print '<div class="body"><h1>Error 404</h1><h2>' . $message . '</h2>';
        if ($refresh) {
            print '<p><small><a href="' . $this->template->get('home') . 'refresh/'
                . $this->encodeLink($refresh) . '">Attempt Refresh</a></small></p>';
        }
        print '</div>';
        $this->template->include('footer');

        exit;
    }
}
