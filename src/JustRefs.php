<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * JustRefs Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

class JustRefs extends Base
{
    private $query = ''; // current query

    /**
     * Route the current web request
     */
    public function route()
    {
        register_shutdown_function(array($this, 'shutdownHandler'));
        ini_set('display_errors', '1');

        $this->startTimer('page');

        $this->initRouter();
        $this->initTemplate();

        $match = $this->router->match();
        if (!$match) {
            $this->error404('Page Not Found'); // exits
        }

        switch ($match) {
            case 'topic':
                $topic = new Topic();
                $topic->template = $this->template;
                $topic->router = $this->router;
                $topic->get();
                break;
            case 'home':
                $this->setQueryFromGet();
                if (!empty($this->query)) {
                    $search = new Search();
                    $search->template = $this->template;
                    $search->get($this->query);
                    break;
                }
                $this->template->include('home');
                break;
            case 'about':
                $this->template->set('title', 'About this site');
                $this->template->include('about');
                break;
            case 'refresh':
                $this->refresh();
                break;
        }
    }

    private function initRouter()
    {
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
    }

    private function initTemplate()
    {
        $this->template = new Template();
        $this->template->verbose = $this->verbose;
        $this->template->timer = $this->timer;
        $this->template->set('home', $this->router->getHome());
        $this->template->set('title', $this->siteName);
        $this->template->set('name', $this->siteName);
        $this->template->set('version', self::VERSION);
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
            $this->template->include('header');
            print '<p>OK - cache deleted</p>'
                . '<p><a href="' . $this->getLink($this->query) . '">' . $this->query . '</a></p>';
            $this->template->include('footer');
            return;
        }

        $this->template->set('title', 'Refresh');
        $this->template->include('header');
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
        $this->template->include('footer');
    }

    /**
     * @param string $message
     * @param string $reresh - refresh query link
     * @return void
     */
    private function error404($message = 'Page Not Found', $refresh = '')
    {
        header('HTTP/1.0 404 Not Found');
        $this->template->include('html_head');
        $this->template->include('header');
        print '<div class="body"><h1>Error 404</h1><h2>' . $message . '</h2>';
        if ($refresh) {
            print '<p><small><a href="' 
            . $this->router->getHome() . 'refresh/' . $this->encodeLink($refresh)
            . '">Attempt Refresh</a></small></p>';
        }
        print '</div>';
        $this->template->include('footer');
        exit;
    }
}
