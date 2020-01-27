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
    private $data = []; // topic data
    private $vars = []; // template variables

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
        $this->initMediawiki();
        $this->data = $this->mediawiki->links($this->topic);
        if (!is_array($this->data)) {
            $this->data = [];
        }
    }

    private function display()
    {
        $this->setTemplateVars();
        // set Extraction source url
        $this->template->set('source', $this->source . $this->encodeLink($this->data['title']));
        // set Data and Cache age
        $this->dataAge = '?';
        $age = $this->filesystem->age($this->data['title']);
        if ($age) {
            $this->dataAge = gmdate('Y-m-d H:i:s', $age);
        }
        $this->template->set('dataAge', $this->dataAge);
        $this->template->set('now', gmdate('Y-m-d H:i:s'));
        $this->template->set('refresh', $this->template->get('home') . 'refresh/' . $this->encodeLink($this->data['title']));
        $this->template->set('h1', $this->data['title']);
        $this->template->set('title', $this->data['title'] . ' - ' . $this->siteName);
        $this->template->include('topic');
    }

    private function setTemplateVars()
    {
        $this->initVars();
        $this->setNamespaces();
        $this->setRefs();
        $this->setTemplates();
        $this->setTemplateExists();
        $this->removeTemplateTopics();

        foreach (array_keys($this->vars) as $index) {
            //$this->verbose('setTemplateVars: vars.' . $index . ' ' . count($this->vars[$index]));
            // set counts
            $this->template->set($index . '_count', count($this->vars[$index]));
            // sort var lists alphabetically
            sort($this->vars[$index]);
            // set html list
            $this->template->set($index . '_list', $this->listify($index, $this->vars[$index]));
            // set template vars
            $this->template->set($index, $this->vars[$index]);
        }
        $this->verbose('setTemplateVars: vars: # ' . count($this->vars));
    }

    private function initVars()
    {
        $ns = [
            'main', 'talk',
            'template', 'template_talk',
            'portal', 'portal_talk',
            'wikipedia', 'wikipedia_talk',
            'help', 'help_talk',
            'module', 'module_talk',
            'draft', 'draft_talk',
            'user', 'user_talk',
            'refs',
            'missing',
            'exists',
            'main_secondary',
            'template_secondary',
        ];
        foreach ($ns as $index) {
            $this->vars[$index] = [];
        }
        $this->verbose('initVars: vars: # ' . count($this->vars));
        //$this->verbose('initVars: vars: ' . print_r($this->vars, true));
    }

    private function setNamespaces()
    {
        $this->verbose('setNamespaces: data.topics: # ' . count($this->data['topics']));
        foreach ($this->data['topics'] as $topic) {
            //$this->verbose('setNamespaces: topic: ' . print_r($topic, true));
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
                case '8':  // Mediawiki
                    break; // exclude
                case '9':  // Mediawiki_talk
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
        $this->verbose('setNamespaces: vars: # ' . count($this->vars));
        $this->verbose('setNamespaces: vars.main: # ' . count($this->vars['main']));
        $this->verbose('setNamespaces: vars.template: # ' . count($this->vars['template']));
        $this->verbose('setNamespaces: vars.portal: # ' . count($this->vars['portal']));
        $this->verbose('setNamespaces: vars.module: # ' . count($this->vars['module']));

    }

    private function setTemplateExists()
    {
        $this->verbose('setTemplateExists: vars.template: # ' . count($this->vars['template']));
        foreach ($this->vars['template'] as $item) {
            //$this->verbose('setTemplateExists: check: ' . $item);
            if ($this->filesystem->exists($item)) {
                $this->vars['exists'][] = $item;
            }
        }
        $this->verbose('setTemplateExists: vars.exists: # ' . count($this->vars['exists']));
        //$this->verbose('setTemplateExists: vars.exists: ' . print_r($this->vars['exists'], true));
    }

    private function setRefs()
    {
        $this->verbose('setRefs: data.refs: # ' . count($this->data['refs']));
        foreach ($this->data['refs'] as $ref) {
            if (substr($ref, 0, 2) == '//') {
                $ref = 'https:' . $ref;
            }
            $this->vars['refs'][] = $ref;
        }
        $this->verbose('setRefs: vars.refs: # ' . count($this->vars['refs']));
    }

    private function setTemplates()
    {
        $this->verbose('setTemplates: data.templates: # ' . count($this->data['templates']));
        foreach ($this->data['templates'] as $item) {
            switch ($item['ns']) {
                case '0': // Main
                    if ($item['*'] != $this->topic) {
                        $this->vars['main'][] = $item['*'];
                    }
                    break;
                case '10': // Template:
                    if (!in_array($item['*'], $this->vars['template'])) {
                        $this->vars['template_secondary'][] = $item['*'];
                    }
                    break;
                case '828': // Module:
                    $this->vars['module'][] = $item['*'];
                    break;
            }
        }
        $this->verbose('setTemplates: vars.main: # ' . count($this->vars['main']));
        $this->verbose('setTemplates: vars.template_secondary: # ' . count($this->vars['template_secondary']));
        $this->verbose('setTemplates: vars.module: # ' . count($this->vars['module']));
    }

    private function removeTemplateTopics() {
        $this->verbose('removeTemplateTopics: vars.main: # ' . count($this->vars['main']));
        $this->verbose('removeTemplateTopics: vars.template: # ' . count($this->vars['template']));
        $this->verbose('removeTemplateTopics: vars.template_secondary: # ' . count($this->vars['template_secondary']));
        if (empty($this->vars['main'])) {
            return;
        }
        if (empty($this->vars['template']) && $this->vars['template_secondary']) {
            return;
        }
        foreach ($this->vars['template'] as $template) {
            $this->verbose('removeTemplateTopics: template: ' . $template);
            if ($template == $this->topic) {
                continue; // self
            }
            if (!in_array($template, $this->vars['exists'])) {
                continue; // template not cached
            }
            $templateData = $this->filesystem->get($template);
            $this->verbose('removeTemplateTopics: templateData: # ' . count($templateData));
            if (empty($templateData['topics']) || !is_array($templateData['topics'])) {
                continue; // error malformed data
            }
            foreach ($templateData['topics'] as $exTopic) {
                if ($exTopic['ns'] == '0') { // main namespace only
                    // remove this template topic from master topic list
                    if (in_array($exTopic['*'], $this->vars['main'])) {
                        unset($this->vars['main'][array_search($exTopic['*'], $this->vars['main'])]);
                        $this->vars['main_secondary'][] = $exTopic['*'];
                        //$this->verbose('removeTemplateTopics: main_secondary: ' . $exTopic['*']);
                    }
                }
            }
        }
        $this->verbose('removeTemplateTopics: vars.main: # ' . count($this->vars['main']));
        $this->verbose('removeTemplateTopics: vars.main_secondary: # ' . count($this->vars['main_secondary']));
    }

    /**
     * @param string $index - vars index
     * @param array $list - array of items
     * @return string - html fragment
     */
    private function listify($index, $list)
    {
        //$this->verbose("listify: index: $index count.list: " . count($list));
        switch ($index) {
            case 'exists':
            case 'missing':
                return '';
        }    
        if (empty($list)) {
            return '';
        }

        $html = '<ol>';
        foreach ($list as $item) {
            //$this->verbose('listify: ' . $item);            
            // Link to external reference
            if ($index == 'refs') {
              $html .= '<li><a href="' . $item . '" target="_blank">' . $item . '</li>';
              continue;
            }

            // non-existing page
            if (in_array($item, $this->vars['missing'])) { 
                $html .= '<li><span class="red">' . $item . '</span></li>';
                continue;
            }

            // Link to internal page
            $class = '';
            if ($index == 'template') {
                if (!in_array($item, $this->vars['exists'])) {
                    $class = ' class="missing"';
                }
            }
            $html .= '<li><a href="' 
                . $this->template->get('home')
                . $this->getLink($item) . '"' 
                . $class . '>' . $item . '</a></li>';
        }
        $html .= '</ol>';
        //$this->verbose("listify: html: $html");

        return $html;
    }
}
