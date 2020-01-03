<?php
declare(strict_types = 1);

namespace Attogram\Justrefs;

class Web extends Base
{
    private $data = [];
    private $filesystem;
    private $router;
    private $query = '';
    private $title = '';

    public function route()
    {
        $this->startTimer('page');
        $this->router = new \Attogram\Router\Router();
        $this->router->allow('/', 'home');
        $this->router->allow('/r/?', 'topic');
        $this->router->allow('/r/?/?', 'topic');
        $this->router->allow('/r/?/?/?', 'topic');
        $this->router->allow('/about', 'about');
        $control = $this->router->match();
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
        // get search query
        $this->query = $this->router->getGet('q');

        // No search query, show homepage
        if (!is_string($this->query) || !strlen(trim($this->query))) {
            $this->htmlHeader();
            include('../templates/home.php');
            $this->htmlFooter();
            return;
        }

        // format search query
        $this->query = trim($this->query);

        // are search results in cache?
        $this->filesystem = new Filesystem();
        $this->filesystem->verbose = $this->verbose;
        $filename = 'search:' . mb_strtolower($this->query);
        $results = $this->filesystem->get($filename);
        if ($results) {
            // get cached results
            $data = @json_decode($results, true);
            if (is_array($data)) {
                // show cached results
                $this->searchResults($data);
                return;
            }
        }

        // get search results from API
        $mediaWiki = new MediaWiki();
        $mediaWiki->verbose = $this->verbose;
        $results = $mediaWiki->search($this->query);
        if ($results) {
            // save results to cache
            $this->filesystem->set($filename, json_encode($results));
            // show api results
            $this->searchResults($results);
            return;
        }

        // no search results
        //header('HTTP/1.0 404 Not Found');
        $this->htmlHeader();
        include('../templates/home.php');
        print '<b>0</b> results';
        $this->htmlFooter();
    }

    /**
     * @param array $data
     */
    private function searchResults($data)
    {
        $this->htmlHeader();
        print '<b>' . count($data) . '</b> results<ol>';
        foreach ($data as $topic) {
            print '<li><a href="r/' . $topic . '">' . $topic . '</a></li>';
        }
        print '</ol>';
        $this->htmlFooter();
    }

    private function topic()
    {
        // get topic query from url
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
        // format topic query
        $this->query = trim($this->query);
        $this->query = str_replace('_', ' ', $this->query);
        $this->query = urldecode($this->query);

        if (!strlen($this->query)) {
            $this->error404('Not Found');
        }

        // is topic in cache?
        $this->filesystem = new Filesystem();
        $this->filesystem->verbose = $this->verbose;
        $results = $this->filesystem->get($this->query);
        if ($results) {
            // get cached results
            $data = @json_decode($results, true);
            if (is_array($data)) {
                // show cached results
                $this->topicPage($data);
                return;
            }
        }

        // get topic from API
        $mediaWiki = new MediaWiki();
        $mediaWiki->verbose = $this->verbose;
        $data = $mediaWiki->links($this->query);
        if ($data) {
            // save results to cache
            $this->filesystem->set($this->query, json_encode($data));
            // show api results
            $this->topicPage($data);
            return;
        }

        $this->error404('Topic Not Found');
    }

    /**
     * @param array $data
     */
    private function topicPage($data)
    {
        $this->verbose('topicPage: data.title: ' . $data['title']);
        $this->verbose('topicPage: count.data.topics: ' . count($data['topics']));
        $this->verbose('topicPage: count.data.refs: ' . count($data['refs']));
        $this->verbose('topicPage: count.data.templates: ' . count($data['templates']));
        $this->htmlHeader();


        // build array of related topics, mainspace topics only
        $topics = [];
        foreach ($data['topics'] as $topic) {
            if ($topic['ns'] == '0') {
                $topics[$topic['*']] = $topic['*'];
            }
        }
        // sort alphabetically
        sort($topics);
        $this->verbose('topicPage: count.topics: ' . count($topics));

        // build array of reference links
        $refs = [];
        foreach ($data['refs'] as $ref) {
            if (substr($ref, 0, 2) == '//') {
                $ref = 'https:' . $ref;
            }
            $refs[] = $ref;
        }
        // sort alphabetically
        sort($refs);
        $this->verbose('topicPage: count.refs: ' . count($refs));

        // build array of templates
        $templates = [];
        $cachedTemplates = [];
        foreach ($data['templates'] as $template) {
            if ($template['ns'] == '10') {
                $templates[] = $template['*'];

                // is template cached?
                $templateJson = $this->filesystem->get($template['*']);
                $templateData = @json_decode($templateJson, true);
                //$this->verbose($templateData);
                if (!empty($templateData['topics']) && is_array($templateData['topics'])) {
                    $cachedTemplates[] = $template['*'];
                    foreach ($templateData['topics'] as $exTopic) {
                        if ($exTopic['ns'] == '0') {
                            // remove this template topic from master topic list
                            if (in_array($exTopic['*'], $topics)) {
                                unset(
                                    $topics[
                                        array_search($exTopic['*'], $topics)
                                    ]
                                );
                                $this->verbose('unset topic: ' . $exTopic['*']);
                            }
                        }
                    }
                }
            }
        }
        // sort alphabetically
        sort($templates);
        $this->verbose('topicPage: count.templates: ' . count($templates));
        $this->verbose('topicPage: count.topics: ' . count($topics));

        // display
        print '<h1>' . $data['title'] . '</h1>';
        print '<div class="flex-container">';
        print '<div class="topics">'
            . '<small><b>' . count($topics) . '</b> Related Topics:</small><ol>';
        foreach ($topics as $topic) {
            print '<li><a href="' . $this->getLink($topic) . '">' . $topic . '</a></li>';
        }
        print '</ol></div>';

        print '<div class="refs">'
            . '<small><b>' . count($refs) . '</b> Reference Links:</small><ol>';
        foreach ($refs as $ref) {
            print '<li><a href="' . $ref . '" target="_blank">' . $ref . '</a></li>';
        }
        $wikipediaUrl = 'https://en.wikipedia.org/wiki/' . $data['title'];

        $wikipediaUrl = str_replace('+', '_', $wikipediaUrl);
        print '</ol></div></div>';


        print '<hr /><small><b>' . count($templates) . '</b> Included Templates:</small><ol>';
        foreach ($templates as $template) {
            $class = in_array($template, $cachedTemplates)
                ? 'cached'
                : 'missing';
            print '<li><a href="' . $this->getLink($template) . '" class="' . $class 
                . '">' . $template . '</a></li>';
        }
        print '</ol>';

        print '<hr /><small>extracted from  &lt;'
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
        $page = str_replace('?', '%3F', $query);
        return $this->router->getHome() . 'r/' . $page;
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
     * @param string $title (optional)
     * @return void
     */
    private function htmlHeader()
    {
        $htmlTitle = strlen($this->title) ? $this->title . ' - ' . $this->siteName : $this->siteName;
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
            . '</head><body>'
            . '<div class="head">' 
            . $this->htmlSiteLink() 
                . '<div style="float:right;">'
                . '<form action="' . $this->router->getHome() . '">'
                . '<input name="q" value="" type="text" size="20">'
                . '<input type="submit" value="search">'
                . '</form>'
                . '</div>'
            . '</div>'
            . '<div class="body">';
    }

    /**
     * @return void
     */
    private function htmlFooter()
    {
        print '</div><footer>' 
            . $this->htmlSiteLink()
            . '<br /><small>page generated in ' . $this->endTimer('page') . ' seconds</small>'
            . '</footer></body></html>';
    }

    /**
     * @return string
     */
    private function htmlSiteLink()
    {
        return '<a href="' . $this->router->getHome() . '">' . $this->siteName . '</a>'
            . ' - <a href="' . $this->router->getHome() . 'about/">About</a>';
    }
}
