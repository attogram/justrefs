<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Topic Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function array_keys;
use function gmdate;
use function in_array;
use function json_encode;
use function sort;
use function substr;

class Topic extends Base
{
    private $topic; // page topic
    private $data = []; // topic data
    private $vars = []; // template vars

    /**
     * Get a topic
     */
    public function get()
    {
        $this->setTopicFromUrl();
        if (!$this->topic) {
            $this->error404('Not Found');
        }

        // get topic from Cache
        $this->setDataFromCache();
        if ($this->data) {
            if (!empty($this->data['error'])) {
                $this->error404('Topic Not Found', $this->topic);
                return;
            }
            $this->display(); // show cached results
            return;
        }

        // get topic from API
        $this->setDataFromApi();
        if ($this->data) {
            // save results to cache
            $this->filesystem->set($this->topic, json_encode($this->data)); 
            if (!empty($this->data['error'])) {
                $this->error404('Topic Not Found', $this->topic);
                return;
            }
            $this->display(); // show api results
            return;
        }

        $this->error404('Topic Not Found');
    }

    private function display()
    {
        // set template variables
        $this->setVarsTopics();
        $this->setVarsRefs();
        $this->setVarsTemplates();
        $this->setVarsExists();
        $this->removeTemplateTopics();
        
        foreach (array_keys($this->vars) as $index) {
            // sort var lists alphabetically
            sort($this->vars[$index]);
            // set template vars
            $this->template->set($index, $this->vars[$index]);
        }

        // set Extraction source url
        $this->template->set('source', 'https://en.wikipedia.org/wiki/' . $this->encodeLink($this->data['title']));

        // set Data and Cache age
        $this->dataAge = '?';
        $age = $this->filesystem->age($this->data['title']);
        if ($age) {
            $this->dataAge = gmdate('Y-m-d H:i:s', $age);
        }
        $this->template->set('dataAge', $this->dataAge);
        $this->template->set('now', gmdate('Y-m-d H:i:s'));

        $this->template->set('refresh', $this->router->getHome() . 'refresh/' . $this->encodeLink($this->data['title']));
        $this->template->set('h1', $this->data['title']);

        $this->template->set('title', $this->data['title'] . ' - ' . $this->siteName);

        $this->template->include('topic');
    }

    /**
     * set $this->data to array from cached file, or empty array
     */
    private function setDataFromCache()
    {
        $this->initFilesystem();
        $this->data = $this->filesystem->get($this->topic);
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
        $this->data = $this->mediaWiki->links($this->topic);
        if (!is_array($this->data)) {
            $this->data = [];
        }
    }

    /**
     * set $this->topic to string from URL elements, or empty string
     */
    private function setTopicFromUrl()
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

    private function setVarsTopics()
    {
        $varIndexi = [
            'missing', 'main', 'template', 'portal', 'wikipedia', 'help', 'module', 'draft',
            'user', 'talk', 'user_talk', 'wikipedia_talk', 'help_talk', 
        ];
        foreach ($varIndexi as $index) {
            $this->vars[$index] = [];
        }

        foreach ($this->data['topics'] as $topic) {
            if (!isset($topic['exists'])) {
                // page does not exist
                $this->vars['missing'][] = $topic['*'];
            }
            // @see https://en.wikipedia.org/wiki/Wikipedia:Namespace
            switch ($topic['ns']) {
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
        $this->vars['technical_template'] = [];

        foreach ($this->data['templates'] as $item) {
            switch ($item['ns']) {
                case '0': // Main
                    if ($item['*'] != $this->topic) {
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
                    break;
            }
        }
    }

    private function setVarsExists()
    {
        $this->vars['exists'] = [];
        foreach ($this->vars['template'] as $item) {
            if ($this->filesystem->exists($item)) {
                $this->vars['exists'][] = $item;
            }
        }
    }

    private function removeTemplateTopics() {
        if (empty($this->vars['main'])) {
            return;
        }
        if (empty($this->vars['template']) && $this->vars['technical_template']) {
            return;
        }
        foreach ($this->vars['template'] as $template) {
            if ($template == $this->topic) {
                continue; // self
            }
            if (!in_array($template, $this->vars['exists'])) {
                continue; // template not cached
            }
            $templateData = $this->filesystem->get($template);
            if (empty($templateData['topics']) || !is_array($templateData['topics'])) {
                continue; // error malformed data
            }
            foreach ($templateData['topics'] as $exTopic) {
                if ($exTopic['ns'] == '0') { // main namespace only
                    // remove this template topic from master topic list
                    if (in_array($exTopic['*'], $this->vars['main'])) {
                        unset($this->vars['main'][array_search($exTopic['*'], $this->vars['main'])]);
                    }
                }
            }
        }
    }
}
