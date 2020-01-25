<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Search Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function count;
use function header;
use function is_array;
use function json_encode;
use function mb_strtolower;

class Search extends Base
{
    private $searchResults; // array of search results

    /**
     * Get search results, from Cache, or Direct API call
     *
     * @param string $query - Search Query
     */
    public function get($query)
    {
        // Get results from cache
        $this->initFilesystem();
        $cachedFile = 'search:' . mb_strtolower($query);
        $this->searchResults = $this->filesystem->get($cachedFile);
        if (!is_array($this->searchResults) || empty($this->searchResults)) {
            // No cached results - Get results from API
            $this->initMediaWiki();
            $this->searchResults = $this->mediaWiki->search($query);
            if ($this->searchResults) { 
                // Got API results - Save results to cache
                $this->filesystem->set($cachedFile, json_encode($this->searchResults));
            }
        }
        $this->display();
    }

    /**
     * Display search results
     */
    private function display()
    {
        if (!is_array($this->searchResults) || empty($this->searchResults)) {
            // No results, set page status to 404
            header('HTTP/1.0 404 Not Found');
            $this->searchResults = [];
        }
        $this->template->set('title', 'search results - ' . $this->siteName);
        $this->template->include('html_head');
        $this->template->include('header');
        print '<div class="body"><b>' . count($this->searchResults) . '</b> results:<ol>';
        foreach ($this->searchResults as $topic) {
            print '<li><a href="' . $this->getLink($topic) . '">' . $topic . '</a></li>';
        }
        print '</ol></div>';
        $this->template->include('footer');
    }
}
