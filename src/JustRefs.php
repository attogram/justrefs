<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * JustRefs Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function is_string;
use function strlen;
use function trim;

class JustRefs extends Base
{
    private $query = ''; // current query

    /**
     * Route the current web request
     */
    public function route()
    {
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
                $topic->verbose = $this->verbose;
                $topic->template = $this->template;
                $topic->router = $this->router; // topic :: setTopicFromUrl() needs router
                $topic->get();
                break;
            case 'home':
                $this->setQueryFromGet();
                if (empty($this->query)) {
                    $this->template->include('home');
                    break;
                }
                $search = new Search();
                $search->template = $this->template;
                $search->get($this->query);
                break;
            case 'about':
                $this->template->set('title', 'About this site');
                $this->template->include('about');
                break;
            case 'refresh':
                $refresh = new Refresh();
                $refresh->template = $this->template;
                $refresh->router = $this->router;
                $refresh->refresh();
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
    }
}
