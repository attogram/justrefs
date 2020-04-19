<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Base Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function get_class;
use function header;
use function htmlentities;
use function is_string;
use function microtime;
use function print_r;
use function round;
use function str_replace;
use function strlen;
use function trim;
use function urldecode;

class Base
{
    const VERSION = '0.5.1';

    /** Cache Time, in seconds.  4 days = 345600 seconds */
    const CACHE_TIME = 345600;

    /** key strings */
    const ABOUT = 'about';
    const ASTERISK = '*';
    const DRAFT = 'draft';
    const DRAFT_TALK = 'draft_talk';
    const EXISTS = 'exists';
    const EXTERNALLINKS = 'externallinks';
    const HELP = 'help';
    const HELP_TALK = 'help_talk';
    const HOME = 'home';
    const LINKS = 'links';
    const MAIN = 'main';
    const MAIN_SECONDARY = 'main_secondary';
    const MISSING = 'missing';
    const MODULE = 'module';
    const MODULE_TALK = 'module_talk';
    const NS = 'ns';
    const PARSE = 'parse';
    const PORTAL = 'portal';
    const PORTAL_TALK = 'portal_talk';
    const QUERY = 'query';
    const REFRESH = 'refresh';
    const REFS = 'refs';
    const SEARCH = 'search';
    const TALK = 'talk';
    const TEMPLATE = 'template';
    const TEMPLATE_TALK = 'template_talk';
    const TEMPLATE_SECONDARY = 'template_secondary';
    const TITLE = 'title';
    const TOPIC = 'topic';
    const TOPICS = 'topics';
    const TEMPLATES = 'templates';
    const USER = 'user';
    const USER_TALK = 'user_talk';
    const WIKIPEDIA = 'wikipedia';
    const WIKIPEDIA_TALK = 'wikipedia_talk';

    /**
     * @var bool - print verbose debug messages to STDOUT
     */
    public $verbose = false;

    /**
     * @var Attogram\Router\Router
     */
    public $router;

    /**
     * @var Attogram\Justrefs\Template
     */
    public $template;

    /**
     * @var Attogram\Justrefs\FilesystemCache
     */
    protected $filesystem;

    /**
     * @var Attogram\Justrefs\Mediawiki
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
    protected $cacheDirectory = '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;

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
     * @param Attogram\Router\Router $router (optional, default null)
     * @param Attogram\Justrefs\Template $template (optional, default null)
     */
    public function __construct($verbose = false, $router = null, $template = null)
    {
        if ($verbose) {
            $this->verbose = true;
        }
        if ($router instanceof \Attogram\Router\Router) {
            $this->router = $router;
        }
        if ($template instanceof Template) {
            $this->template = $template;
        }
    }

    /**
     * Verbose debug statement to STDOUT
     * @param string $message (optional)
     */
    protected function verbose($message = '')
    {
        if ($this->verbose) {
            print '<pre>' . $this->formatMessage($message) . '</pre>';
        }
    }

    /**
     * Error statement to STDOUT
     * @param string $message (optional)
     */
    protected function error($message = '')
    {
        print '<pre>ERROR: ' . $this->formatMessage($message) . '</pre>';
    }

    /**
     * @param string $message (optional)
     */
    private function formatMessage($message = '')
    {
        return (new \DateTime())->format('u') . ': ' . get_class($this)
            . ': ' . htmlentities(print_r($message, true));
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
     * @return bool
     */
    protected function setTopicFromUrl()
    {
        $this->topic = $this->router->getVar(0);
        $this->topic .= !empty($this->router->getVar(1)) ? '/' . $this->router->getVar(1) : '';
        $this->topic .= !empty($this->router->getVar(2)) ? '/' . $this->router->getVar(2) : '';
        $this->topic .= !empty($this->router->getVar(3)) ? '/' . $this->router->getVar(3) : '';
        $this->topic .= !empty($this->router->getVar(4)) ? '/' . $this->router->getVar(4) : '';

        if (!is_string($this->topic) || !strlen($this->topic)) {
            $this->topic = '';
    
            return false;
        }
        // format query
        $this->topic = trim($this->topic);
        $this->topic = str_replace('_', ' ', $this->topic);
        $this->topic = urldecode($this->topic);
        if (!is_string($this->topic) || !strlen($this->topic)) {
            $this->topic = '';

            return false;
        }

        return true;
    }

    protected function initFilesystem()
    {
        $this->filesystem = (new FilesystemCache())->init();
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
