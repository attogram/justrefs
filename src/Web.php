<?php
declare(strict_types = 1);

namespace Attogram\Justrefs;

class Web
{
    const VERSION = '0.0.2';

    public $verbose = false;

    private $config = [];
    private $router;
    private $filesystem;
    private $query = false;
    private $data = false;
    private $title = false;

    /**
     * @param bool $verbose (optional, default: false)
     */
    public function __construct($verbose = false)
    {

        $this->config['name'] = 'Just Refs';
        $this->config['cache'] = '..' . DIRECTORY_SEPARATOR . 'cache';
        //$this->config['api'] = 'https://en.wikipedia.org/w/api.php?action=parse&prop=externallinks|links&format=json&page=';

        if ($verbose) {
            $this->verbose = true;
        }
        $this->verbose(get_class($this) . ' v' . self::VERSION);

        $this->filesystem = new Filesystem();
        $this->filesystem->verbose = $this->verbose;
    }

    public function route()
    {
        $this->router = new \Attogram\Router\Router();
        $this->router->allow('/', 'home');
        $this->router->allow('/r/?', 'topic');
        $this->router->allow('/r/?/?', 'topic');
        $this->router->allow('/r/?/?/?', 'topic');
        $this->router->allow('/about', 'about');
        $control = $this->router->match();
        //$this->verbose("route: control: $control");
        if (!$control || !method_exists($this, $control)) {
            $this->error404('Page Not Found');
        }
        $this->{$control}();
    }

    private function about()
    {
        $this->title = 'About this site';
        $this->htmlHeader();
        include('../templates/about.php');
        $this->htmlFooter();
    }

    private function home()
    {
        $this->query = $this->router->getGet('q');
        if (!is_string($this->query) || !strlen($this->query)) {
            $this->htmlHeader();
            include('../templates/home.php');
            $this->htmlFooter();
            return;
        }
        $mediaWiki = new MediaWiki();
        $mediaWiki->verbose = $this->verbose;
        $results = $mediaWiki->search($this->query);
        if (!$results || !is_array($results)) {
            header('HTTP/1.0 404 Not Found');
            $this->htmlHeader();
            include('../templates/home.php');
            print '<p>0 Results</p>';
            $this->htmlFooter();
            return;
        }
        $this->htmlHeader();
        print '<b>' . count($results) . '</b> results<ol>';
        foreach ($results as $result) {
            print '<li><a href="r/' . urlencode($result) . '">' . $result . '</a></li>';
        }
        print '</ol>';
        $this->htmlFooter();
    }

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
        if (!strlen($this->query)) {
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
        print '</ol></div></div>';
        print '<small>extracted from  &lt;'
            . '<a href="' . $wikipediaUrl . '" target="_blank">' 
            . $wikipediaUrl . '</a>&gt; released under the '
            //. '<a href="https://creativecommons.org/licenses/by-sa/3.0/" target="_blank">'
            . 'Creative Commons Attribution-Share-Alike License 3.0'
            //. '</a>'
            . '</small>';

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
     * @return bool
     * @sets $this->data
     */
    private function getData()
    {
        $this->verbose('getData: query: ' . $this->query);
        if (!$this->filesystem->exists($this->query)) {
            $this->verbose('getData: file not found');
            if (!$this->getDataFromApi()) {
                $this->verbose('getData: no data from api');
                return false;
            }
        }
        $cached = $this->filesystem->get($this->query);
        if (empty($cached) || !is_string($cached)) {
            $this->verbose('getData: file get failed');
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
     * @sets $this->title
     */
    private function getDataFromApi()
    {
        $this->verbose('getDataFromApi: query: ' . $this->query);
        $page = str_replace(' ', '%20', $this->query);

        $mediaWiki = new MediaWiki();
        $mediaWiki->verbose = $this->verbose;
        $data = $mediaWiki->links($this->query);
        if (empty($data)) {
            $this->verbose('getDataFromApi: false: no data');
            return false;
        }
        $this->title = $data['parse']['title'];
        $this->verbose('getDataFromApi: title: ' . $this->title);
        if ($this->filesystem->set($this->title, json_encode($data))) {
            return true;
        }
        $this->verbose('getDataFromApi: filesystem set failed');
        return false;
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
            print '<pre>' . gmdate('Y-m-d H:i:s') . ': Wiki: ' . htmlentities(print_r($message, true)) . '</pre>';
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
