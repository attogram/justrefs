<?php
/**
 * Just Refs
 * Mediawiki Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

class Mediawiki extends Base
{
    private $userAgent = 'JustRefsBot/' . self::VERSION;
    private $api = 'https://en.wikipedia.org/w/api.php';
    private $apiLinks = '?action=parse&prop=externallinks|links|templates&format=json&page=';
    private $apiSearch = '?action=query&list=search&format=json&srprop=&srlimit=50&srsearch=';

    /**
     * @param string $query
     * @return array|false
     */
    public function links($query)
    {
        $url = $this->api . $this->apiLinks . urlencode($query);
        $jsonData = $this->getApi($url);
        if (!$jsonData) {
            $this->verbose('links: ERROR: getApi call failed: ' . $url);
            return false;
        }

        $data = @json_decode($jsonData, true);
        if (empty($data) || !is_array($data)) {
            $this->verbose('links: ERROR: decode failed: ' . $query);
            return false;
        }

        $result = [];

        if (isset($data['error'])
            || !isset($data['parse']['title']) 
            || empty($data['parse']['title'])
        ) {
            $result['title'] = $query;
            $result['error'] = true;
            $this->verbose('links: ERROR: NOT FOUND: ' . $query);
            return $result;
        }

        // set title
        $result['title'] = $data['parse']['title'];

        // set related topics 
        $result['topics'] = isset($data['parse']['links'])
            ? $data['parse']['links']
            : [];

        // set reference links
        $result['refs'] = isset($data['parse']['externallinks'])
            ? $data['parse']['externallinks']
            : [];
        
        // set templates
        $result['templates'] = isset($data['parse']['templates'])
            ? $data['parse']['templates']
            : [];

        return $result;
    }

    /**
     * @param string $query
     * @return array|false
     */
    public function search($query)
    {
        $url = $this->api . $this->apiSearch . urlencode($query);
        $jsonData = $this->getApi($url);
        if (!$jsonData) {
            return false;
        }
        $data = @json_decode($jsonData, true);
        if (empty($data)
            || !is_array($data)
            || empty($data['query'])
            || empty($data['query']['search'])
            || !is_array($data['query']['search'])
        ) {
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
     * @return string|false
     */
    private function getApi($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent); 
        $result = curl_exec($ch);
        curl_close($ch);
        if (empty($result)) {
            $this->verbose('getApi: ERROR: EMPTY RESULT: ' . $url);
            return false;
        }
        $this->verbose('getApi: OK: ' . $url . ' - ' . strlen($result));
        $this->verbose($result);
        return $result;
    }
}
