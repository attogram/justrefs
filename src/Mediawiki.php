<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Mediawiki Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function curl_close;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function is_array;
use function is_string;
use function json_decode;
use function print_r;
use function urlencode;

class Mediawiki extends Base
{
    /**
     * @var string - user agent for API requests
     */
    private $userAgent = 'JustRefsBot/' . self::VERSION;

    /**
     * @var string - api endpoint
     */
    private $api = 'https://en.wikipedia.org/w/api.php';

    /**
     * @var string - url params to get page links
     */
    private $apiLinks = '?action=parse&prop=externallinks|links|templates&format=json&page=';

    /**
     * @var string - url params for search request
     */
    private $apiSearch = '?action=query&list=search&format=json&srprop=&srlimit=50&srsearch=';

    /**
     * @param string $query
     * @return array|false
     */
    public function links($query)
    {
        $data = $this->getApi($this->api . $this->apiLinks . urlencode($query));
        if (!$data || !is_array($data)) {
            $this->error('links: decode failed: ' . $query);

            return false;
        }
        $result = [];
        if (isset($data['error'])
            || !isset($data['parse']['title'])
            || empty($data['parse']['title'])
        ) {
            $result['title'] = $query;
            $result['error'] = true;
            $this->error('links: 404 NOT FOUND: ' . $query);

            return $result;
        }
        // set title
        $result['title'] = $data['parse']['title'];
        // set reference links
        $result['refs'] = isset($data['parse']['externallinks']) ? $data['parse']['externallinks']: [];
        // set related topics
        $result['topics'] = isset($data['parse']['links']) ? $data['parse']['links'] : [];
        // set templates
        $result['templates'] = isset($data['parse']['templates']) ? $data['parse']['templates'] : [];

        return $result;
    }

    /**
     * @param string $query
     * @return array|false
     */
    public function search($query)
    {
        $data = $this->getApi($this->api . $this->apiSearch . urlencode($query));
        if (!$data || !is_array($data) || empty($data['query'])
            || empty($data['query']['search']) || !is_array($data['query']['search'])
        ) {
            $this->error('search: query failed');

            return false;
        }
        $results = [];
        foreach ($data['query']['search'] as $topic) {
            if (!isset($topic['title']) || !is_string($topic['title'])) {
                continue;
            }
            $results[] = $topic['title'];
        }

        return $results;
    }

    /**
     * @param string $url
     * @return array|false
     */
    private function getApi($url)
    {
        if (empty($url) || !is_string($url)) {
            $this->error('getApi: invalid url: ' . print_r($url, true));
            return false;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        $jsonData = curl_exec($curl);
        curl_close($curl);
        if (empty($jsonData)) {
            $this->error("getApi: EMPTY RESULT: $url");

            return false;
        }
        $data = @json_decode($jsonData, true);
        if (!is_array($data)) {
            $this->error("getApi: DECODE FAILED: url: $url jsonData: $jsonData");

            return false;
        }
        $this->verbose("getApi: got: $url");

        return $data;
    }
}
