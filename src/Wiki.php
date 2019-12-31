<?php
/**
 * Raw Wiki
 */
declare(strict_types = 1);

namespace Raw;

class Wiki
{
    const VERSION = '0.0.1';

    public $verbose = false;

    private $config = [];
    private $router;
    private $query = false;
    private $filename = false;
    private $data = false;
    private $title = false;

    /**
     * @param bool $verbose (optional, default: false)
     * @sets $this->router
     * @sets $this->config
     * @sets $this->verbose
     */
    public function __construct($verbose = false)
    {
        $this->router = new \Attogram\Router\Router();
        $this->config['name'] = 'Just Refs';
        $this->config['cache'] = '..' . DIRECTORY_SEPARATOR . 'cache';
        $this->config['api'] = 'https://en.wikipedia.org/w/api.php?action=parse&prop=externallinks|links&format=json&page=';
        // &curtimestamp=1
        // &redirects=1
        // http://en.citizendium.org/api.php?action=parse&prop=externallinks|links&format=json&page=

        if ($verbose) {
            $this->verbose = true;
        }
        $this->verbose(get_class($this) . ' v' . self::VERSION);
    }

    /**
     * @return void
     */
    public function route()
    {
        $this->router->allow('/', 'home');
        $this->router->allow('/r/?', 'topic');
        $this->router->allow('/r/?/?', 'topic');
        $this->router->allow('/r/?/?/?', 'topic');
        $this->router->allow('/about', 'about');
        $control = $this->router->match();
        $this->verbose("route: control: $control");
        if (!$control || !method_exists($this, $control)) {
            $this->error404('Page Not Found');
        }
        $this->{$control}();
    }

    /**
     * @return void
     * @sets $this->query
     */
    private function home()
    {
        $this->query = $this->router->getGet('q');

        if (empty($this->query)) {
            $this->htmlHeader();
            include('../templates/home.php');
            $this->htmlFooter();

            return;
        }

        if (!$this->getFilename()) {
            $this->filename = $this->getDataFromApi();
        }

        if (!$this->filename) {
            header('HTTP/1.0 404 Not Found');
            $this->htmlHeader();
            include('../templates/home.php');
            print '<p>0 Results</p>';
            $this->htmlFooter();

            return;
        }

        $this->router->redirect($this->getLink());
    }

    private function about()
    {
        $this->title = 'About this site';
        $this->htmlHeader();
        include('../templates/about.php');
        $this->htmlFooter();
    }

    /**
     * @return void
     * @sets $this->query
     */
    private function topic()
    {
        $this->query = $this->router->getVar(0);
        if ($this->router->getVar(1)) {
            $this->query .= '/' . $this->router->getVar(1);
            if ($this->router->getVar(2)) {
                $this->query .= '/' . $this->router->getVar(2);
                if ($this->router->getVar(3)) {
                    $this->query .= '/' . $this->router->getVar(3);
                }
            }
        }

        $this->query = trim($this->query);
        $this->query = str_replace('_', ' ', $this->query);
        $this->query = urldecode($this->query);
        $this->verbose('topic: query: ' . $this->query);

        if (!$this->query) {
            $this->error404('Not Found');
        }

        if (!$this->getData()) {
            $this->error404('Topic Not Found');
        }

        $this->topicPage();
    }

    /**
     * @return void
     */
    private function topicPage()
    {
        $this->verbose('topicPage: count.data: ' . count($this->data));
        $this->htmlHeader();
        $links = $this->data['parse']['links'];
        $exernalLinks = $this->data['parse']['externallinks'];
        $name = $this->data['parse']['title'];

        print '<h1>' . $name . '</h1>';

        print '<div class="flex-container">';

        print '<div>Topics:<ol>';
        foreach ($links as $link) {
            if ($link['ns'] == '0') {
                print '<li><a href="' 
                    . $this->getLink($link['*']) . '">' 
                    . $link['*'] . '</a></li>';
            }
        }
        print '</ol></div>';

        print '<div>Links:<ol>';
        foreach ($exernalLinks as $link) {
            print '<li><a href="' . $link . '" target="_blank">' . $link . '</a></li>';
        }
        $wikipediaUrl = 'https://en.wikipedia.org/wiki/' . urlencode($name);
        $wikipediaUrl = str_replace('+', '_', $wikipediaUrl);
        print '<li><a href="' . $wikipediaUrl . '" target="_blank">' . $wikipediaUrl . '</a></li>';
        print '</ol></div>';

        print '</div>';

        $this->htmlFooter();
    }

    /**
     * @return string
     */
    private function getLink($query = '')
    {
        if (!$query) {
            $query = $this->query;
        }
        $page = str_replace(' ', '_', $query);
        $page = urlencode($page);

        return $this->router->getHome() . 'r/' . $page;
    }

    /**
     * @param string $this->query
     * @return string
     */
    private function buildFilename()
    {
        return $this->config['cache'] . DIRECTORY_SEPARATOR . md5($this->query) . '.json';
    }

    /**
     * @param string $this->query
     * @return bool
     * @sets $this->filename
     * @sets $this->title
     */
    private function getFilename()
    {
        // raw query
        $this->title = $this->query;
        $this->filename = $this->buildFilename($this->title);
        if (is_readable($this->filename)) {

            return true;
        }
        // Uppercase-first-letter of first word only
        $this->title = ucfirst(strtolower($this->query));
        $this->filename = $this->buildFilename($this->title);
        if (is_readable($this->filename)) {
            return true;
        }
        // Upper-case-first-letter of all words
        $this->title = ucwords(strtolower($this->query));
        $this->filename = $this->buildFilename($this->title);
        if (is_readable($this->filename)) {
            return true;
        }
    
        $this->title = false;
        $this->filename = false;

        return false;
    }

    /**
     * @return bool
     * @sets $this->data
     */
    private function getData()
    {

        $this->verbose("getData: query: {$this->query}");

        if (!$this->getFilename()) {
            if (!$this->getDataFromApi()) {
                $this->verbose('getData: no data found');
                return false;
            }
        }

        $this->verbose("getData: filename: {$this->filename}");

        $cached = @file_get_contents($this->filename);

        if (empty($cached) || !is_string($cached)) {
            $this->verbose('getData: file_get_contents failed');
            return false;
        }
    
        $this->data = @json_decode($cached, true);

        if (empty($this->data) || !is_array($this->data)) {
            $this->data = false;
            $this->verbose('getData: json_decode failed');
            return false;
        }

        $this->verbose('getData: data.count: ' . count($this->data));
        return true;
    }

    /**
     * @param string $this->query
     * @return bool
     * @sets $this->filename
     * @sets $this->title
     */
    private function getDataFromApi()
    {
        //$this->verbose("getDataFromApi($this->query)");

        $page = str_replace(' ', '%20', $this->query);

        $url = $this->config['api'] . $page;

        $this->verbose("getDataFromApi: $url");

        $jsonData = file_get_contents($url);
        if (empty($jsonData)) {
            $this->verbose('getDataFromApi: file get failed');
            return false;
        }

        $array = @json_decode($jsonData, true);

        if (empty($array) || !is_array($array)) {
            $this->verbose('getDataFromApi: decode failed');
            return false;
        }

        if (empty($array['parse'])
            || empty($array['parse']['title'])
            || !isset($array['parse']['links'])
            || !is_array($array['parse']['links'])
            || !isset($array['parse']['externallinks'])
            || !is_array($array['parse']['externallinks'])
        ) {
            $this->verbose('getDataFromApi: missing elements');
            return false;
        }

        $this->filename = $this->buildFilename($array['parse']['title']);
        $this->title = $array['parse']['title'];

        $this->verbose('getDataFromApi: title: ' . $this->title . ' filename: ' . $this->filename);

        $bytes = file_put_contents($this->filename, $jsonData);
        if (!$bytes) {
            $this-verbose('getDataFromApi: file put failed');
            return false;
        }

        $this->verbose("getDataFromApi: wrote: $bytes bytes");

        return true;
    }

    /**
     * @param string $message
     * @return void
     */
    private function error404($message = 'Page Not Found')
    {
        header('HTTP/1.0 404 Not Found');
        $this->htmlHeader();
        print '<h1>Error 404</h1><h2>' . $message . '</h2>';
        $this->htmlFooter();
        exit;
    }

    /**
     * @param string $message
     * @return void
     */
    private function verbose($message)
    {
        if ($this->verbose) {
            print '<pre>' . gmdate('Y-m-d H:i:s') . ': ' . htmlentities(print_r($message, true)) . '</pre>';
        }
    }

    /**
     * @param string $title (optional)
     * @return void
     */
    private function htmlHeader()
    {
        $htmlTitle = !empty($this->title) 
            ? $this->title . ' - ' . $this->config['name']
            : $this->config['name'];

        print '<!doctype html>' . "\n"
            . '<html lang="en"><head>'
            . '<meta charset="UTF-8">'
            . '<meta http-equiv="X-UA-Compatible" content="IE=edge">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<link rel="apple-touch-icon" sizes="180x180" href="' . $this->router->getHome() . 'apple-touch-icon.png">'
            . '<link rel="icon" type="image/png" sizes="32x32" href="' . $this->router->getHome() . 'favicon-32x32.png">'
            . '<link rel="icon" type="image/png" sizes="16x16" href="' . $this->router->getHome() . 'favicon-16x16.png">'
            . '<link rel="manifest" href="' . $this->router->getHome() . 'site.webmanifest">'
            . '<link rel="stylesheet" href="' . $this->router->getHome() . 'style.css">'
            . '<title>' . $htmlTitle . '</title>' 
            . '</head><body><div class="head">'
            . $this->htmlSiteLink()
            . ' - <a href="' . $this->router->getHome() . 'about/">About</a>'
            . '</div><div class="body">';
    }

    /**
     * @return void
     */
    private function htmlFooter()
    {
        print '</div>'
            . '<footer>' . $this->htmlSiteLink() 
            . ' - <a href="' . $this->router->getHome() . 'about/">About</a>'
            . '</footer></body></html>';
    }

    /**
     * @return string
     */
    private function htmlSiteLink()
    {
        return '<a href="' . $this->router->getHome() . '">' . $this->config['name'] . '</a>';
    }
}

