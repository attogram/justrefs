<?php
/**
 * Just Refs
 * Web Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function array_search;
use function chr;
use function count;
use function gmdate;
use function header;
use function in_array;
use function intval;
use function is_array;
use function is_readable;
use function is_string;
use function json_encode;
use function mb_strtolower;
use function rand;
use function str_replace;
use function sort;
use function strlen;
use function substr;
use function time;
use function trim;
use function urldecode;

class Web extends Base
{
    public $vars = []; // template vars

    private $data = []; // topic data
    private $filesystem; // Attogram\Justrefs\Filesystem
    private $mediaWiki; // Attogram\Justrefs\MediaWiki
    private $query = ''; // current query
    private $router; // Attogram\Router\Router
    private $title = ''; // current page title

    /**
     * Route the current web request
     */
    public function route()
    {
        $this->startTimer('page');
        $this->router = new \Attogram\Router\Router();
        $this->router->allow('/', 'home');
        $this->router->allow('/r/?', 'topic');
        $this->router->allow('/r/?/?', 'topic');
        $this->router->allow('/r/?/?/?', 'topic');
        $this->router->allow('/r/?/?/?/?', 'topic');
        $this->router->allow('/about', 'about');
        $this->router->allow('/refresh', 'refresh');
        $this->router->allow('/refresh/?', 'refresh');
        $this->router->allow('/refresh/?/?', 'refresh');
        $this->router->allow('/refresh/?/?/?', 'refresh');
        $this->router->allow('/refresh/?/?/?/?', 'refresh');
        switch ($this->router->match()) {
            case 'topic':
                $this->topic();
                break;
            case 'home':
                $this->setQueryFromGet();
                if ($this->query) {
                    $this->search();
                    break;
                }
                $this->homepage();
                break;
            case 'about':
                $this->about();
                break;
            case 'refresh':
                $this->refresh();
                break;
            default:
                $this->error404('Page Not Found');
                break;
        }
    }

    private function homepage()
    {
        $this->title = $this->siteName;
        $this->includeTemplate('header');
        $this->includeTemplate('home');
        $this->includeTemplate('footer');
    }

    private function about()
    {
        $this->title = 'About this site';
        $this->includeTemplate('header');
        $this->includeTemplate('about');
        $this->includeTemplate('footer');
    }

    private function search()
    {
        // get search results from cache
        $this->initFilesystem();
        $filename = 'search:' . mb_strtolower($this->query);
        $this->data = $this->filesystem->get($filename);
        if (is_array($this->data)) {
            $this->searchResults($this->data); // show results from cached file
            return;
        }
        // get search results from MediaWiki API
        $this->initMediaWiki();
        $this->data = $this->mediaWiki->search($this->query);
        if ($this->data) {
            $this->filesystem->set($filename, json_encode($this->data)); // save results to cache
            $this->searchResults(); // show results from api response
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

    private function topic()
    {
        $this->setQueryFromUrl();
        if (!$this->query) {
            $this->error404('Not Found');
        }

        // get topic from Cache
        $this->setDataFromCache();
        if ($this->data) {
            if (!empty($this->data['error'])) {
                $this->error404('Topic Not Found', $this->query);
                return;
            }
            $this->topicPage(); // show cached results
            return;
        }

        // get topic from API
        $this->setDataFromApi();
        if ($this->data) {
            $this->filesystem->set($this->query, json_encode($this->data)); // save results to cache
            if (!empty($this->data['error'])) {
                $this->error404('Topic Not Found', $this->query);
                return;
            }
            $this->topicPage(); // show api results
            return;
        }

        $this->error404('Topic Not Found');
    }

    /**
     * set $this->data to array from cached file, or empty array
     */
    private function setDataFromCache()
    {
        $this->initFilesystem();
        $this->data = $this->filesystem->get($this->query);
        if (!is_array($this->data)) {
            $this->data = [];
        }
    }

    /**
     * set $this->data to array from api response, or empty array
     */
    private function setDataFromApi()
    {
        $this->initMediaWiki();
        $this->data = $this->mediaWiki->links($this->query);
        if (!is_array($this->data)) {
            $this->data = [];
        }
    }

    /**
     * set $this->query to string from _GET['q'], or empty string
     */
    private function setQueryFromGet()
    {
        $this->query = $this->router->getGet('q');
        if (!$this->query || !is_string($this->query)) {
            $this->query = '';
            return;
        }
        $this->query = trim($this->query);
        if (!strlen($this->query)) {
            $this->query = '';
            return;
        }
        return $this->query;
    }

    /**
     * set $this->query to string from URL elements, or empty string
     */
    private function setQueryFromUrl()
    {
        $this->query = $this->router->getVar(0);
        if ($this->router->getVar(1)) {
            $this->query .= '/' . $this->router->getVar(1);
            if ($this->router->getVar(2)) {
                $this->query .= '/' . $this->router->getVar(2);
                if ($this->router->getVar(3)) {
                    $this->query .= '/' . $this->router->getVar(3);
                    if ($this->router->getVar(4)) {
                        $this->query .= '/' . $this->router->getVar(4);
                    }
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

    private function topicPage()
    {
        // set template variables
        $this->setVarsTopics();
        $this->setVarsRefs();
        $this->setVarsTemplates();
        $this->setVarsExists();
        $this->removeTemplateTopics();
        
        // sort var lists alphabetically
        foreach (array_keys($this->vars) as $index) {
            sort($this->vars[$index]);
        }

        // set Extraction source url
        $this->vars['source'] = 'https://en.wikipedia.org/wiki/' . $this->encodeLink($this->data['title']);

        // set Data and Cache age
        $this->dataAge = '?';
        $age = $this->filesystem->age($this->data['title']);
        if ($age) {
            $this->dataAge = gmdate('Y-m-d H:i:s', $age);
        }
        $this->vars['dataAge'] = $this->dataAge;
        $this->vars['now'] = gmdate('Y-m-d H:i:s');

        $this->vars['refresh'] = $this->router->getHome() . 'refresh/' . $this->encodeLink($this->data['title']);
        $this->vars['h1'] = $this->data['title'];

        // Display page
        $this->title = $this->data['title'] . ' - ' . $this->siteName;
        $this->includeTemplate('header');
        $this->includeTemplate('topic');
        $this->includeTemplate('footer');
    }

    private function setVarsTopics()
    {
        $this->vars['main'] = [];
        $this->vars['template'] = [];
        $this->vars['portal'] = [];
        $this->vars['wikipedia'] = [];
        $this->vars['help'] = [];
        $this->vars['module'] = [];
        $this->vars['draft'] = [];
        $this->vars['user'] = [];
        $this->vars['talk'] = [];
        $this->vars['user_talk'] = [];
        $this->vars['wikipedia_talk'] = [];
        $this->vars['help_talk'] = [];
        //$this->vars[''] = [];

        foreach ($this->data['topics'] as $topic) {
            switch ($topic['ns']) { // @see https://en.wikipedia.org/wiki/Wikipedia:Namespace
                case '0':  // Mainspace
                    $this->vars['main'][] = $topic['*'];
                    break;
                case '1':  // Talk
                    $this->vars['talk'][] = $topic['*'];
                    break;
                case '2':  // User
                    $this->vars['user'][] = $topic['*'];
                    break;
                case '3':  // User_talk
                    $this->vars['user_talk'][] = $topic['*'];
                    break;
                case '4':  // Wikipedia
                    $this->vars['wikipedia'][] = $topic['*'];
                    break;
                case '5':  // Wikipedia_talk
                    $this->vars['wikipedia_talk'][] = $topic['*'];
                    break;
                case '6':  // File
                    break; // exclude
                case '7':  // File_talk
                    break; // exclude
                case '8':  // MediaWiki
                    break; // exclude
                case '9':  // MediaWiki_talk
                    break; // exclude
                case '10': // Template
                    $this->vars['template'][] = $topic['*'];
                    break;
                case '11': // Template_talk
                    $this->vars['template_talk'][] = $topic['*'];
                    break;
                case '12': // Help
                    $this->vars['help'][] = $topic['*'];
                    break;
                case '13': // Help_talk
                    $this->vars['help_talk'][] = $topic['*'];
                    break;
                case '14': // Category
                    break; // exclude
                case '15': // Category_talk
                    break; // exclude
                case '100': // Portal
                    $this->vars['portal'][] = $topic['*'];
                    break;
                case '101': // Portal_talk
                    $this->vars['portal_talk'][] = $topic['*'];
                    break;
                case '108': // Book
                    break;
                case '109': // Book_talk
                    break;
                case '118': // Draft
                    $this->vars['draft'][] = $topic['*'];
                    break;
                case '119': // Draft_talk
                    $this->vars['draft_talk'][] = $topic['*'];
                    break;
                case '710': // TimedText
                    break; // exclude
                case '711': // TimedText_talk
                    break; // exclude
                case '828': // Module
                    $this->vars['module'][] = $topic['*'];
                    break;
                case '829': // Module_talk
                    $this->vars['module_talk'][] = $topic['*'];
                    break;
                default:
                    break; // exclucde
            }                
        }
        $this->verbose('setVarsTopics: # this.vars: ' . count($this->vars));
        $this->verbose('setVarsTopics: # vars.main: ' . count($this->vars['main']));
        $this->verbose('setVarsTopics: # vars.template: ' . count($this->vars['template']));

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
        $this->verbose('setVarsRefs: # vars.refs: ' . count($this->vars['refs']));

    }

    private function setVarsTemplates()
    {
        $this->vars['technical_template'] = [];

        foreach ($this->data['templates'] as $item) {
            switch ($item['ns']) {
                case '0': // Main
                    if ($item['*'] != $this->query) {
                        $this->vars['main'][] = $item['*'];
                    }
                    break;
                case '10': // Template:
                    if (!in_array($item['*'], $this->vars['template'])) {
                        $this->vars['technical_template'][] = $item['*'];
                    }
                    break;
                case '828': // Module:
                    $this->vars['module'][] = $item['*'];
                    break;
                default:
                    $this->verbose('setVarsTemplates: default: item: ' . $item['*']);
                    //$this->verbose('setVarsTemplates: default: ns: ' . $item['ns']);
                    break;
            }
        }
        //$this->verbose('setVarsTemplates: # vars.main: ' . count($this->vars['main']));
        //$this->verbose('setVarsTemplates: # vars.technical_template: ' . count($this->vars['technical_template']));
        //$this->verbose('setVarsTemplates: # vars.module: ' . count($this->vars['module']));

    }

    private function setVarsExists()
    {
        $this->vars['exists'] = [];
        //foreach (array_merge($this->vars['template'], $this->vars['technical_template']) as $item) {
        foreach ($this->vars['template'] as $item) {
            if ($this->filesystem->exists($item)) {
                $this->vars['exists'][] = $item;
            }
        }
        $this->verbose('setVarsExists: # vars.exists: ' . count($this->vars['exists']));
    }

    private function removeTemplateTopics() {
        //$this->verbose('removeTemplateTopics');
        if (empty($this->vars['main'])) {
            $this->verbose('removeTemplateTopics: empty vars.main');
            return;
        }
        if (empty($this->vars['template']) && $this->vars['technical_template']) {
            $this->verbose('removeTemplateTopics: empty vars.template and vars.technical_template');
            return;
        }
        //foreach (array_merge($this->vars['template'], $this->vars['technical_template']) as $template) {
        foreach ($this->vars['template'] as $template) {
            if ($template == $this->query) {
                $this->verbose('removeTemplateTopics: self');
                continue; // self
            }
            if (!in_array($template, $this->vars['exists'])) {
                continue; // template not cached
            }
            $templateData = $this->filesystem->get($template);
            if (empty($templateData['topics']) || !is_array($templateData['topics'])) {
                $this->verbose('removeTemplateTopics: ERROR: malformed data');
                continue; // error malformed data
            }
            foreach ($templateData['topics'] as $exTopic) {
                if ($exTopic['ns'] == '0') { // main namespace only
                    // remove this template topic from master topic list
                    if (in_array($exTopic['*'], $this->vars['main'])) {
                        //$this->verbose('removeTemplateTopics: ' . $template . ' unset: ' . $exTopic['*']);
                        unset($this->vars['main'][array_search($exTopic['*'], $this->vars['main'])]);
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
     * @param string $message
     * @param string $reresh - refresh query link
     * @return void
     */
    private function error404($message = 'Page Not Found', $refresh = '')
    {
        header('HTTP/1.0 404 Not Found');
        $this->includeTemplate('header');
        print '<h1>Error 404</h1><h2>' . $message . '</h2>';
        if ($refresh) {
            print '<p><small><a href="' 
            . $this->router->getHome() . 'refresh/' . $this->encodeLink($refresh)
            . '">Attempt Refresh</a></small></p>';
        }
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

    protected function initFilesystem()
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->verbose = $this->verbose;
    }

    private function initMediaWiki()
    {
        $this->mediaWiki = new MediaWiki();
        $this->mediaWiki->verbose = $this->verbose;
    }

    /**
     * @return string
     */
    public function getLink($query = '')
    {
        if (!$query) {
            $query = $this->query;
        }
        return $this->router->getHome() . 'r/' . $this->encodeLink($query);
    }
}
