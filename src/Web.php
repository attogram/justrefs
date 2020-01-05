<?php
/**
 * Just Refs
 * Web Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

class Web extends Base
{
    private $data = []; // topic data
    private $filesystem; // Attogram\Justrefs\Filesystem
    private $mediaWiki; // Attogram\Justrefs\MediaWiki
    private $query = ''; // current query
    private $router; // Attogram\Router\Router
    private $title = ''; // current page title
    private $vars = []; // template vars

    public function route()
    {
        $this->startTimer('page');
        $this->router = new \Attogram\Router\Router();
        $this->router->allow('/', 'home');
        $this->router->allow('/r/?', 'topic');
        $this->router->allow('/r/?/?', 'topic');
        $this->router->allow('/r/?/?/?', 'topic');
        $this->router->allow('/about', 'about');
        $this->router->allow('/refresh', 'refresh');
        $this->router->allow('/refresh/?', 'refresh');
        $this->router->allow('/refresh/?/?', 'refresh');
        $this->router->allow('/refresh/?/?/?', 'refresh');
        $control = $this->router->match();
        if (!$control || !method_exists($this, $control)) {
            $this->error404('Page Not Found');
        }
        $this->{$control}();
    }

    private function about()
    {
        $this->title = 'About this site';
        $this->includeTemplate('header');
        $this->includeTemplate('about');
        $this->includeTemplate('footer');
    }

    private function home()
    {
        $this->query = $this->router->getGet('q'); // get search query

        if (!is_string($this->query) || !strlen(trim($this->query))) {
            // No search query, show homepage
            $this->title = $this->siteName;
            $this->includeTemplate('header');
            $this->includeTemplate('home');
            $this->includeTemplate('footer');
            return;
        }

        $this->query = trim($this->query); // format search query

        // are search results in cache?
        $this->initFilesystem();
        $filename = 'search:' . mb_strtolower($this->query);
        $this->data = $this->filesystem->get($filename);
        if (is_array($this->data)) {
            $this->searchResults($this->data); // show cached results
            return;
        }
        
        // get search results from MediaWiki API
        $this->initMediaWiki();
        $this->data = $this->mediaWiki->search($this->query);
        if ($this->data) {
            $this->filesystem->set($filename, json_encode($this->data)); // save results to cache
            $this->searchResults(); // show api results
            return;
        }

        // no search results
        header('HTTP/1.0 404 Not Found');
        $this->title = $this->siteName;
        $this->includeTemplate('header');
        $this->includeTemplate('home');
        print '<b>0</b> results';
        $this->includeTemplate('footer');
    }

    private function searchResults()
    {
        $this->title = 'search results - ' . $this->siteName;
        $this->includeTemplate('header');
        print '<b>' . count($this->data) . '</b> results<ol>';
        foreach ($this->data as $topic) {
            print '<li><a href="' . $this->getLink($topic) . '">' . $topic . '</a></li>';
        }
        print '</ol>';
        $this->includeTemplate('footer');
    }

    private function setQueryFromUrl()
    {
        // get query from url
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
        if (!is_string($this->query) || !strlen($this->query)) {
            $this->query = '';
            return;
        }
        // format query
        $this->query = trim($this->query);
        $this->query = str_replace('_', ' ', $this->query);
        $this->query = urldecode($this->query);
        if (!is_string($this->query) || !strlen($this->query)) {
            $this->query = '';
        }
    }

    private function topic()
    {
        $this->setQueryFromUrl();
        if (!strlen($this->query)) {
            $this->error404('Not Found');
        }

        // is topic in cache?
        $this->initFilesystem();
        $this->data = $this->filesystem->get($this->query);
        if (is_array($this->data)) {
            $this->topicPage($this->data); // show cached results
            return;
        }

        // get topic from API
        $this->initMediaWiki();
        $this->data = $this->mediaWiki->links($this->query);
        if ($this->data) {
            $this->filesystem->set($this->query, json_encode($this->data)); // save results to cache
            $this->topicPage($this->data); // show api results
            return;
        }

        $this->error404('Topic Not Found');
    }

    private function topicPage()
    {
        // set template variables
        $this->setVarsTopics();
        $this->setVarsRefs();
        $this->setVarsTemplates();
        $this->setVarsMetaInformation();
        $this->removeTemplateTopics();
        
        // sort lists alphabetically
        sort($this->vars['topics']);
        sort($this->vars['topics_internal']);
        sort($this->vars['refs']);
        sort($this->vars['templates']);

        // extraction source url
        $this->vars['source'] = 'https://en.wikipedia.org/wiki/' . $this->encodeLink($this->data['title']);

        // Data and Cache age
        $this->dataAge = '?';
        $age = $this->filesystem->age($this->data['title']);
        if ($age) {
            $this->dataAge = gmdate('Y-m-d H:i:s', $age);
        }
        $this->vars['dataAge'] = $this->dataAge;
        $this->vars['now'] = gmdate('Y-m-d H:i:s');

        $this->vars['refresh'] = $this->router->getHome() . 'refresh/' . $this->encodeLink($this->data['title']);
        $this->vars['h1'] = $this->data['title'];

        // display page
        $this->title = $this->data['title'] . ' - ' . $this->siteName;
        $this->includeTemplate('header');
        $this->includeTemplate('topic');
        $this->includeTemplate('footer');
    }

    private function setVarsTopics()
    {
        $this->vars['topics'] = [];
        $this->vars['topics_internal'] = [];
        foreach ($this->data['topics'] as $topic) {
            switch ($topic['ns']) { // @see https://en.wikipedia.org/wiki/Wikipedia:Namespace
                case '0': // Mainspace
                    $this->vars['topics'][$topic['*']] = $topic['*'];
                    break;
                case '6': // File
                case '14': // Category
                    break; // exclude
                default:
                    $this->vars['topics_internal'][$topic['*']] = $topic['*'];
                    break;
            }                
        }
    }

    private function setVarsRefs()
    {
        $this->vars['refs'] = [];
        foreach ($this->data['refs'] as $ref) {
            if (substr($ref, 0, 2) == '//') {
                $ref = 'https:' . $ref;
            }
            $this->vars['refs'][] = $ref;
        }
    }

    private function setVarsTemplates()
    {
        $this->vars['templates'] = [];
        foreach ($this->data['templates'] as $template) {
            if ($template['ns'] == '10') {
                $this->vars['templates'][] = $template['*'];
            }
        }
    }

    private function setVarsMetaInformation()
    {
        $this->verbose(
            'setVarsMetaInformation:'
            //. ' topics:' . count($this->vars['topics'])
            . ' topics_internal:' . count($this->vars['topics_internal']) 
            . ' templates:' . count($this->vars['templates'])
        );
        $this->vars['meta'] = [];
        //foreach ($this->vars['topics'] as $topic) {
        //    $this->vars['meta'][$topic]['exists'] = $this->filesystem->exists($topic);
        //}
        foreach ($this->vars['topics_internal'] as $topic) {
            if (substr($topic, 0, 9) == 'Template:') {
                $this->vars['meta'][$topic]['exists'] = $this->filesystem->exists($topic);
            }
        }
        foreach ($this->vars['templates'] as $topic) {
            if (substr($topic, 0, 9) == 'Template:') {
                $this->vars['meta'][$topic]['exists'] = $this->filesystem->exists($topic);
            }
        }
    }

    private function removeTemplateTopics() {
        foreach ($this->vars['templates'] as $template) {
            if ($template == $this->query) {
                continue; // self
            }
            if (empty($this->vars['meta'][$template]['exists'])) {
                continue; // template not cached
            }

            $templateData = $this->filesystem->get($template);

            if (empty($templateData['topics']) || !is_array($templateData['topics'])) {
                continue; // error malformed data
            }

            foreach ($templateData['topics'] as $exTopic) {
                if ($exTopic['ns'] == '0') { // main namespace only
                    // remove this template topic from master topic list
                    if (in_array($exTopic['*'], $this->vars['topics'])) {
                        //$this->verbose('removeTemplateTopics: unset: ' . $exTopic['*']);
                        unset($this->vars['topics'][array_search($exTopic['*'], $this->vars['topics'])]);
                    }
                }
            }
        }
    }

    private function refresh()
    {
        $this->setQueryFromUrl();
        if (!strlen($this->query)) {
            $this->error404('Refresh Topic Not Found');
        }

        $this->initFilesystem();

        // does cache file exist?
        if (!$this->filesystem->exists($this->query)) {
            $this->error404('Cache File Not Found');
        }

        if (!empty($_POST)) {
            $answer = isset($_POST['d']) ? $_POST['d'] : '';
            if (!strlen($answer)) {
                $this->error404('Answer Not Found');
            }

            $submitTime = !empty($_POST['c']) ? intval($_POST['c']) : false;
            if (!$submitTime || (time() - $submitTime) > 60) {
                $this->error404('Request Timed Out');
            }

            $one = isset($_POST['a']) ? $_POST['a'] : '';
            $two = isset($_POST['b']) ? $_POST['b'] : '';
            if (!strlen($one) || !strlen($two)) {
                $this->error404('Invalid Request');
            }

            if (($one + $two) != $answer) {
                $this->error404('Invalid Answer');
            }
            
            if (!$this->filesystem->delete($this->query)) {
                $this->error404('Deletion Failed');
            }
            $this->includeTemplate('header');
            print '<p>OK - cache deleted</p>'
                . '<p><a href="' . $this->getLink($this->query) . '">' . $this->query . '</a></p>';
            $this->includeTemplate('footer');
            return;
        }

        $this->title = 'Refresh';
        $this->includeTemplate('header');
        print '<p><b><a href="' . $this->getLink($this->query) . '">' . $this->query 
            . '</a></b> is currently cached.</p>';

        $letterOne = chr(rand(65,90));
        $numOne = rand(0, 10);
        $letterTwo = chr(rand(65,90));
        $numTwo = rand(0, 10);
        $answer = $numOne + $numTwo;

        print '<form method="POST">'
            . '<input type="hidden" name="a" value="' . $numOne . '">'
            . '<input type="hidden" name="b" value="' . $numTwo . '">'
            . '<input type="hidden" name="c" value="' . time() . '">'
            . "If $letterOne = $numOne and $letterTwo = $numTwo"
            . " then  $letterOne + $letterTwo = "
            . '<input name="d" value="" size="4">'
            . '<br /><br />'
            . '<input type="submit" value="    Delete Cache    ">'
            . '</form>';

        $this->includeTemplate('footer');
    }

    /**
     * @param string $query
     * @return string
     */
    private function encodeLink($query)
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

    /**
     * @return string
     */
    private function getLink($query = '')
    {
        if (!$query) {
            $query = $this->query;
        }
        return $this->router->getHome() . 'r/' . $this->encodeLink($query);
    }

    /**
     * @param string $message
     * @return void
     */
    private function error404($message = 'Page Not Found')
    {
        header('HTTP/1.0 404 Not Found');
        $this->includeTemplate('header');
        print '<h1>Error 404</h1><h2>' . $message . '</h2>';
        $this->includeTemplate('footer');
        exit;
    }

    /**
     * @param string $name
     */
    private function includeTemplate($name)
    {
        $template = '../templates/' . $name . '.php';
        if (is_readable($template)) {
            include($template);
            return;
        }
        $this->verbose('includeTemplate: ERROR NOT FOUND: ' . $template);
    }

    private function initFilesystem()
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->verbose = $this->verbose;
    }

    private function initMediaWiki()
    {
        $this->mediaWiki = new MediaWiki();
        $this->mediaWiki->verbose = $this->verbose;
    }
}
