<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
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
    const ERROR_NOT_FOUND  = 'Topic Not Found';

    /** @var array $data - topic data */
    private $data = [];

    /** @var array $vars - template variables */
    private $vars = [];

    /**
     * Get a topic
     * @return void
     */
    public function get()
    {
        if (!$this->setTopicFromUrl()) { // build topic from URL
            $this->error404(self::ERROR_NOT_FOUND);
        }

        $this->initFilesystem();
        
        if ($this->setDataFromCache()) { // get topic data from Cache
            if (!empty($this->data['error'])) {
                $this->error404(self::ERROR_NOT_FOUND, $this->topic);

                return;
            }
            $this->display(); // show cached results

            return;
        }

        // If can get topic data from API
        if ($this->setDataFromApi()) {
            // save results to cache for CACHE_TIME seconds
            $this->filesystem->set($this->topic, json_encode($this->data), self::CACHE_TIME);
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
        $this->template->set('source', $this->source . $this->encodeLink($this->data[self::TITLE]));
    
        // set Data and Cache age
        $filesystemCache = new FilesystemCache();
        $this->template->set('dataAge', $filesystemCache->getAge($this->data[self::TITLE]));
        $this->template->set('now', gmdate('Y-m-d H:i:s'));
        $this->template->set(
            'refresh',
            $this->template->get('home') . 'refresh/' . $this->encodeLink($this->data[self::TITLE])
        );
        $this->template->set('h1', $this->data[self::TITLE]);
        $this->template->set(self::TITLE, $this->data[self::TITLE] . ' - ' . $this->siteName);
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
            self::MAIN, self::TALK, self::MAIN_SECONDARY, self::TEMPLATE, self::TEMPLATE_TALK, self::TEMPLATE_SECONDARY,
            self::PORTAL, self::PORTAL_TALK, self::WIKIPEDIA, self::WIKIPEDIA_TALK, self::HELP, self::HELP_TALK,
            self::MODULE, self::MODULE_TALK, self::DRAFT, self::DRAFT_TALK, self::USER, self::USER_TALK,
            self::REFS, self::MISSING, self::EXISTS,
        ];
        foreach ($namespaces as $index) {
            $this->vars[$index] = [];
        }
    }

    private function setNamespaces()
    {
        foreach ($this->data[self::TOPICS] as $topic) {
            if (!isset($topic[self::EXISTS])) {
                $this->vars[self::MISSING][] = $topic[self::ASTERISK]; // page does not exist
            }
            switch ($topic[self::NS]) { // @see https://en.wikipedia.org/wiki/Wikipedia:Namespace
                case '0':  // Mainspace
                    $this->vars[self::MAIN][] = $topic[self::ASTERISK];
                    break;
                case '1':  // Talk
                    $this->vars[self::TALK][] = $topic[self::ASTERISK];
                    break;
                case '2':  // User
                    $this->vars[self::USER][] = $topic[self::ASTERISK];
                    break;
                case '3':  // User_talk
                    $this->vars[self::USER_TALK][] = $topic[self::ASTERISK];
                    break;
                case '4':  // Wikipedia
                    $this->vars[self::WIKIPEDIA][] = $topic[self::ASTERISK];
                    break;
                case '5':  // Wikipedia_talk
                    $this->vars[self::WIKIPEDIA_TALK][] = $topic[self::ASTERISK];
                    break;
                case '10': // Template
                    $this->vars[self::TEMPLATE][] = $topic[self::ASTERISK];
                    break;
                case '11': // Template_talk
                    $this->vars[self::TEMPLATE_TALK][] = $topic[self::ASTERISK];
                    break;
                case '12': // Help
                    $this->vars[self::HELP][] = $topic[self::ASTERISK];
                    break;
                case '13': // Help_talk
                    $this->vars[self::HELP_TALK][] = $topic[self::ASTERISK];
                    break;
                case '100': // Portal
                    $this->vars[self::PORTAL][] = $topic[self::ASTERISK];
                    break;
                case '101': // Portal_talk
                    $this->vars[self::PORTAL_TALK][] = $topic[self::ASTERISK];
                    break;
                case '118': // Draft
                    $this->vars[self::DRAFT][] = $topic[self::ASTERISK];
                    break;
                case '119': // Draft_talk
                    $this->vars[self::DRAFT_TALK][] = $topic[self::ASTERISK];
                    break;
                case '828': // Module
                    $this->vars[self::MODULE][] = $topic[self::ASTERISK];
                    break;
                case '829': // Module_talk
                    $this->vars[self::MODULE_TALK][] = $topic[self::ASTERISK];
                    break;
                default:
                    break;
            }
        }
    }

    private function setTemplateExists()
    {
        foreach ($this->vars[self::TEMPLATE] as $item) {
            if ($this->filesystem->has($item)) {
                $this->vars[self::EXISTS][] = $item;
            }
        }
    }

    private function setRefs()
    {
        foreach ($this->data[self::REFS] as $ref) {
            if (substr($ref, 0, 2) == '//') {
                $ref = 'https:' . $ref;
            }
            $this->vars[self::REFS][] = $ref;
        }
    }

    private function setTemplates()
    {
        foreach ($this->data['templates'] as $item) {
            switch ($item[self::NS]) {
                case '0': // Main
                    if ($item[self::ASTERISK] != $this->topic) {
                        $this->vars[self::MAIN][] = $item[self::ASTERISK];
                    }
                    break;
                case '10': // Template:
                    if (!in_array($item[self::ASTERISK], $this->vars[self::TEMPLATE])) {
                        $this->vars[self::TEMPLATE_SECONDARY][] = $item[self::ASTERISK];
                    }
                    break;
                case '828': // Module:
                    $this->vars[self::MODULE][] = $item[self::ASTERISK];
                    break;
                default:
                    break;
            }
        }
    }

    private function removeTemplateTopics()
    {
        if (empty($this->vars[self::MAIN])
            || (empty($this->vars[self::TEMPLATE]) && $this->vars[self::TEMPLATE_SECONDARY])
        ) {
            return;
        }
        foreach ($this->vars[self::TEMPLATE] as $template) {
            if ($template == $this->topic || (!in_array($template, $this->vars[self::EXISTS]))) {
                continue; // error: is self, or template not cached
            }
            $templateData = $this->filesystem->get($template);
            if (empty($templateData[self::TOPICS]) || !is_array($templateData[self::TOPICS])) {
                continue; // error: malformed data
            }
            foreach ($templateData[self::TOPICS] as $exTopic) {
                if ($exTopic[self::NS] == '0' && in_array($exTopic[self::ASTERISK], $this->vars[self::MAIN])) {
                    // main namespace only - remove this template topic from master topic list
                    unset($this->vars[self::MAIN][array_search($exTopic[self::ASTERISK], $this->vars[self::MAIN])]);
                    $this->vars[self::MAIN_SECONDARY][] = $exTopic[self::ASTERISK];
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
        if (in_array($index, [self::EXISTS, self::MISSING]) // skip internal-usage vars
            || empty($this->vars[$index]) // Error - index not found, or index empty
        ) {
            return '&nbsp;';
        }
        $html = '<ol>';
        foreach ($this->vars[$index] as $item) {
            if ($index == self::REFS) { // Link to external reference
                $html .= '<li><a href="' . $item . '" target="_blank">' . $item . '</a></li>';
                continue;
            }
            if (in_array($item, $this->vars[self::MISSING])) { // non-existing page
                $html .= '<li><span class="red">' . $item . '</span></li>';
                continue;
            }
            // Link to internal page
            $class = '';
            if ($index == self::TEMPLATE && !in_array($item, $this->vars[self::EXISTS])) {
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
