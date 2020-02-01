<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * JustRefs Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use Attogram\Router\Router;

use function is_string;
use function strlen;
use function trim;

class JustRefs extends Base
{
    /**
     * @var string - the current query
     */
    private $query = '';

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
        $this->match($match);
    }

    /**
     * @param string $match
     */
    private function match($match)
    {
        switch ($match) {
            case 'topic':
                (new Topic($this->verbose, $this->router, $this->template))->get();
                break;
            case 'home':
                $this->setQueryFromGet();
                if (empty($this->query)) {
                    $this->template->include('home');
                    break;
                }
                (new Search($this->verbose, null, $this->template))->get($this->query);
                break;
            case 'about':
                $this->template->set('title', 'About this site');
                $this->template->include('about');
                break;
            case 'refresh':
                (new Refresh($this->verbose, $this->router, $this->template))->get();
                break;
            default:
                break;
        }
    }

    private function initRouter()
    {
        $this->router = new Router();
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
        $this->template = new Template($this->verbose);
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
