<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Topic Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function array_keys;
use function count;
use function gmdate;
use function in_array;
use function is_array;
use function json_encode;
use function sort;
use function substr;

class Topic extends Base
{
    private $data = []; // topic data
    private $vars = []; // template variables

    const ERROR_NOT_FOUND  = 'Topic Not Found';

    /**
     * Get a topic
     * @return void
     */
    public function get()
    {
        if (!$this->setTopicFromUrl()) { // build topic from URL
            $this->error404(self::ERROR_NOT_FOUND);
        }

        if ($this->setDataFromCache()) { // get topic data from Cache
            if (!empty($this->data['error'])) {
                $this->error404(self::ERROR_NOT_FOUND, $this->topic);

                return;
            }
            $this->display(); // show cached results

            return;
        }

        if ($this->setDataFromApi()) { // get topic data from API
            $this->filesystem->set($this->topic, json_encode($this->data)); // save results to cache
            if (!empty($this->data['error'])) { // if API reported an error
                $this->error404(self::ERROR_NOT_FOUND, $this->topic);

                return;
            }
            $this->display(); // show api results

            return;
        }

        $this->error404(self::ERROR_NOT_FOUND);
    }

    /**
     * set $this->data to array from cached file, or empty array
     * @return bool
     */
    private function setDataFromCache()
    {
        $this->initFilesystem();
        $this->data = $this->filesystem->get($this->topic);
        if (!is_array($this->data)) {
            $this->data = [];

            return false;
        }

        return true;
    }

    /**
     * set $this->data to array from api response, or empty array
     * @return bool
     */
    private function setDataFromApi()
    {
        $this->initMediawiki();
        $this->data = $this->mediawiki->links($this->topic);
        if (!is_array($this->data)) {
            $this->data = [];

            return false;
        }

        return true;
    }

    private function display()
    {
        $this->setTemplateVars();
        // set Extraction source url
        $this->template->set('source', $this->source . $this->encodeLink($this->data['title']));
        // set Data and Cache age
        $dataAge = '?';
        $age = $this->filesystem->age($this->data['title']);
        if ($age) {
            $dataAge = gmdate('Y-m-d H:i:s', $age);
        }
        $this->template->set('dataAge', $dataAge);
        $this->template->set('now', gmdate('Y-m-d H:i:s'));
        $this->template->set(
            'refresh',
            $this->template->get('home') . 'refresh/' . $this->encodeLink($this->data['title'])
        );
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
            $this->template->set($index . '_count', count($this->vars[$index])); // set counts
            sort($this->vars[$index]); // sort var lists alphabetically
            $this->template->set($index . '_list', $this->listify($index)); // set html list
        }
    }

    private function initVars()
    {
        $namespaces = [
            'main', 'talk', 'main_secondary',
            'template', 'template_talk', 'template_secondary',
            'portal', 'portal_talk',
            'wikipedia', 'wikipedia_talk',
            'help', 'help_talk',
            'module', 'module_talk',
            'draft', 'draft_talk',
            'user', 'user_talk',
            'refs',
            'missing',
            'exists',
        ];
        foreach ($namespaces as $index) {
            $this->vars[$index] = [];
        }
    }

    private function setNamespaces()
    {
        foreach ($this->data['topics'] as $topic) {
            if (!isset($topic['exists'])) {
                $this->vars['missing'][] = $topic['*']; // page does not exist
            }
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
                case '100': // Portal
                    $this->vars['portal'][] = $topic['*'];
                    break;
                case '101': // Portal_talk
                    $this->vars['portal_talk'][] = $topic['*'];
                    break;
                case '118': // Draft
                    $this->vars['draft'][] = $topic['*'];
                    break;
                case '119': // Draft_talk
                    $this->vars['draft_talk'][] = $topic['*'];
                    break;
                case '828': // Module
                    $this->vars['module'][] = $topic['*'];
                    break;
                case '829': // Module_talk
                    $this->vars['module_talk'][] = $topic['*'];
                    break;
                default:
                    break;
            }
        }
    }

    private function setTemplateExists()
    {
        foreach ($this->vars['template'] as $item) {
            if ($this->filesystem->exists($item)) {
                $this->vars['exists'][] = $item;
            }
        }
    }

    private function setRefs()
    {
        foreach ($this->data['refs'] as $ref) {
            if (substr($ref, 0, 2) == '//') {
                $ref = 'https:' . $ref;
            }
            $this->vars['refs'][] = $ref;
        }
    }

    private function setTemplates()
    {
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
                default:
                    break;
            }
        }
    }

    private function removeTemplateTopics()
    {
        if (empty($this->vars['main'])) {
            return;
        }
        if (empty($this->vars['template']) && $this->vars['template_secondary']) {
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
                if ($exTopic['ns'] == '0' && in_array($exTopic['*'], $this->vars['main'])) {
                    // main namespace only - remove this template topic from master topic list
                    unset($this->vars['main'][array_search($exTopic['*'], $this->vars['main'])]);
                    $this->vars['main_secondary'][] = $exTopic['*'];
                }
            }
        }
    }

    /**
     * @param string $index - this->vars index
     * @return string - html fragment
     */
    private function listify($index)
    {
        if (in_array($index, ['exists', 'missing']) // skip internal-usage vars
            || empty($this->vars[$index]) // Error - index not found, or index empty
        ) {
            return '&nbsp;';
        }
        $html = '<ol>';
        foreach ($this->vars[$index] as $item) {
            if ($index == 'refs') {
                // Link to external reference
                $html .= '<li><a href="' . $item . '" target="_blank">' . $item . '</a></li>';
                continue;
            }
            if (in_array($item, $this->vars['missing'])) {
                // non-existing page
                $html .= '<li><span class="red">' . $item . '</span></li>';
                continue;
            }
            // Link to internal page
            $class = '';
            if ($index == 'template' && !in_array($item, $this->vars['exists'])) {
                // template is not loaded, thus possible that secondary-topics not all set
                $class = ' class="missing"';
            }
            $html .= '<li><a href="'
                . $this->template->get('home') . $this->getLink($item) . '"' . $class . '>'
                . $item . '</a></li>';
        }

        return $html . '</ol>';
    }
}
